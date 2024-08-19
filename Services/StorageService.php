<?php

namespace RFlatti\ShopifyApp\Services;

use Illuminate\Support\Facades\Session;

class StorageService
{
    protected $sessionKey = 'stored_ids';

    public function addId($id)
    {
        $ids = Session::get($this->sessionKey, []);
        $ids[] = $id;
        Session::put($this->sessionKey, $ids);
    }

    public function getIds()
    {
        return Session::get($this->sessionKey, []);
    }

    public function removeId($id)
    {
        $ids = Session::get($this->sessionKey, []);
        if (($key = array_search($id, $ids)) !== false) {
            unset($ids[$key]);
        }
        Session::put($this->sessionKey, array_values($ids));
    }

    public function clear()
    {
        Session::forget($this->sessionKey);
    }
}
