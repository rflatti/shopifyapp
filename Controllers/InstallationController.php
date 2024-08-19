<?php

namespace RFlatti\ShopifyApp\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use RFlatti\ShopifyApp\Services\StorageService;
use RFlatti\ShopifyApp\Models\Store;
use RFlatti\ShopifyApp\Traits\FunctionTrait;
use RFlatti\ShopifyApp\Traits\RequestTrait;

class InstallationController extends Controller
{
    use FunctionTrait, RequestTrait;

    public function __construct(
        protected StorageService $storageService
    ){}

    public function startInstallation(Request $request){
        try {
            if($this->validateRequestFromShopify($request->all())){
                if($request->has('shop')){
                    $storeDetails = $this->getStoreByDomain($request->shop);
                    if($storeDetails !== null && $storeDetails !== false){
                        if($this->checkIfAccessTokenIsValid($storeDetails)){
                            //make it easier to handle the rest of the shopify app ...
                            $this->storageService->addId($storeDetails['id']);
                            return response($this->executeAfterClass($storeDetails, $request));
                        } else {
                            //redirect user to the re-installation process
                            Log::info('Re installation for shop: '.$request->shop);
                            $endpoint = "https://{$request->shop}/admin/oauth/authorize" .
                                "?client_id=" . config('shopify.shopify_api_key') .
                                "&scope=" . config('shopify.api_scopes') .
                                "&redirect_uri=" . route('app_install_redirect');
                            return Redirect::to($endpoint);
                        }
                    } else {
                        //new installation flow should be started
                        // oauth flow url:
                        // https://{shop}.myshopify.com/admin/oauth/authorize?client_id={api_key}&redirect_uri={redirect_uri}&state={nonce}&grant_options[]={access_mode}
                        Log::info('New installation for shop: '.$request->shop);
                        $endpoint = "https://{$request->shop}/admin/oauth/authorize" .
                            "?client_id=" . config('shopify.shopify_api_key') .
                            "&scope=" . config('shopify.api_scopes') .
                            "&redirect_uri=" . route('app_install_redirect');

                        return Redirect::to($endpoint);
                    }
                } else {
                    throw new \Exception('Shop parameter is not presented in the app');
                }
            } else {
                throw new \Exception('Request is not valid');
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            dd($e->getMessage());
        }
    }
    public function handleRedirect(Request $request){
        try{
            if($this->validateRequestFromShopify($request->all())){
                if($request->has('shop') && $request->has('code')){
                    $shop = $request->shop;
                    $code = $request->code;

                    $accessToken = $this->requestAccessTokenFromShopifyForThisStore($shop, $code);
                    if($accessToken !== false && $accessToken !== null){
                        $shopDetails = $this->getShopDetailsFromShopify($shop, $accessToken);
                        $saveDetails = $this->saveStoreDetailsToDatabase($shopDetails, $accessToken);
                        if($saveDetails){
                            //Installation process is completed
                            $storePath = explode('.', $shopDetails['myshopify_domain'])[0];
                            return Redirect::to("https://admin.shopify.com/store/$storePath/apps/".config('shopify.handle'));
                        } else {
                            Log::info('Error saving shop details');
                            Log::info($saveDetails);
                        }
                    } else {
                        throw new \Exception('Invalid access token: '.$accessToken);
                    }
                } else {
                    throw new \Exception('Code / Shop param not presented in the Url');
                }
            } else {
                throw new \Exception('Request is not valid');
            }
        } catch (\Exception $e){
            Log::info($e->getMessage().' '.$e->getLine());
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    public function completeInstallation(Request $request){
        print_r('Installation complete');exit;
    }
    private function validateRequestFromShopify($request): bool
    {
        try {
            $arr = [];
            $hmac = $request['hmac'];
            unset($request['hmac']);

            foreach($request as $key=>$value){

                $key=str_replace("%","%25",$key);
                $key=str_replace("&","%26",$key);
                $key=str_replace("=","%3D",$key);
                $value=str_replace("%","%25",$value);
                $value=str_replace("&","%26",$value);

                $arr[] = $key."=".$value;
            }

            $str = implode('&', $arr);
            $ver_hmac =  hash_hmac('sha256', $str, config('shopify.shopify_api_secret'), false);

            return $ver_hmac==$hmac;

        } catch (\Exception $e){
            Log::info('Problem with verify hmac from request');
            Log::info($e->getMessage().' '.$e->getLine());
            return false;
        }
    }

    private function checkIfAccessTokenIsValid($storeDetails): bool
    {
        try{
            if(
                $storeDetails !== null &&
                isset($storeDetails->access_token) &&
                strlen($storeDetails->access_token) > 0
            ){
                $endpoint = $this->getShopifyURLForStore('shop.json', $storeDetails);
                $headers = $this->getShopifyHeadersForStore($storeDetails);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers, null);

                return $response['statusCode'] === 200;
            }
            return false;
        } catch (\Exception $e){
            return false;
        }
    }

    private function requestAccessTokenFromShopifyForThisStore($shop, $code){
        try{
            $endpoint = "https://$shop/admin/oauth/access_token";
            $headers = $this->getShopifyHeadersForStore(['Content-Type' => 'application/json']);
            $requestBody = [
                'client_id' => config('shopify.shopify_api_key'),
                'client_secret' => config('shopify.shopify_api_secret'),
                'code' => $code,
            ];
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $requestBody);
            if($response['statusCode'] == 200){
                $body = $response['body'];
                if(!is_array($body)) $body = json_decode($body, true);
                if(isset($body['access_token'])){
                    return $body['access_token'];
                }
            }
            return false;
        } catch (\Exception $e){
            return false;
        }
    }

    private function getShopDetailsFromShopify($shop, $accessToken){
        try{
            $endpoint = $this->getShopifyURLForStore('shop.json', $shop);
            $headers = $this->getShopifyHeadersForStore(['access_token' => $accessToken]);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if($response['statusCode'] == 200){
                if(!is_array($response['body'])) $body = json_decode($response['body'], true);
                return $body['shop'] ?? null;
            } else {
                Log::info('Response received for shop details');
                Log::info(json_encode($response));
                return null;
            }
        } catch (\Exception $e) {
            Log::info('Problem getting the shop details from shopify');
            Log::info($e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    private function saveStoreDetailsToDatabase($shopDetails, $accessToken){
        try{
            $payload = [
                'access_token' => $accessToken,
                'myshopify_domain' => $shopDetails['myshopify_domain'],
                'id' => $shopDetails['id'],
                'name' => $shopDetails['name'],
                'phone' => $shopDetails['phone'],
                'address1' => $shopDetails['address1'],
                'address2' => $shopDetails['address2'],
                'zip' => $shopDetails['zip']
            ];
            Store::updateOrCreate(['myshopify_domain' => $shopDetails['myshopify_domain']], $payload);
            return true;
        } catch (\Exception $e){
            Log::info($e->getMessage().' '. $e->getLine());
            return false;
        }
    }

    private function getShopifyHeadersForStore($storeDetails){
        return [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $storeDetails['access_token'] ?? null,
        ];
    }

    private function getShopifyURLForStore($endpoint, $store, $apiVersion = '2023-10'){

        if(isset($store->myshopify_domain)){
            $store_domain = $store->myshopify_domain;
        } else {
            $store_domain = $store;
        }

        return 'https://'.$store_domain.'/admin/api/'.config('shopify.shopify_api_version').'/'.$endpoint;
    }

    /*
     * Add class for further way
     */

    private function executeAfterClass($storeDetails, $request)
    {

        $className = Config::get('shopify.access_token_valid_controller.class');

        // Check if class exists
        if (class_exists($className)) {
            $reflectionClass = new \ReflectionClass($className);

            $constructor = $reflectionClass->getConstructor();
            $dependencies = [];

            if ($constructor) {
                foreach ($constructor->getParameters() as $parameter) {
                    $dependencyClass = $parameter->getClass();

                    if ($dependencyClass) {
                        $dependencies[] = $this->resolveDependency($dependencyClass->name);
                    } else {
                        $dependencies[] = null;
                    }
                }
            }

            $afterClassInstance = $reflectionClass->newInstanceArgs($dependencies);

            if (method_exists($afterClassInstance, 'execute')) {
                return $afterClassInstance->execute($storeDetails, $request);
            }
        } else {
            throw new \Exception("Class $className does not exist.");
        }
    }


    /**
     * @throws \ReflectionException
     */
    private function resolveDependency(string $className)
    {

        //load class dependencies to make constructor working for plugins
        $reflectionClass = new \ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if (is_null($constructor)) {
            return new $className;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $dependencyClass = $parameter->getClass();

            if ($dependencyClass) {
                //load dependencies
                $dependencies[] = $this->resolveDependency($dependencyClass->name);
            } else {
                //no dependencies to load
                $dependencies[] = $parameter->isOptional() ? $parameter->getDefaultValue() : null;
            }
        }

        return $reflectionClass->newInstanceArgs($dependencies);
    }



}
