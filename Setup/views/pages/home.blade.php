@extends('layouts.default')

@section('content')
    i am your homepage
    {{ json_encode($store_id) }}
@endsection
