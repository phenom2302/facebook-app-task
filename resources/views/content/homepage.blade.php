@extends('layouts.layout',['title'=>'Welcome'])
@section('body')
    <div class="jumbotron">
        <h1>Welcome!</h1>
        @if(!session(\App\User::USER_ACCESS_TOKEN_SESSION_NAME))
            <p class="lead">Please click the button below to login with Facebook.</p>
            <p><a class="btn btn-lg btn-success" href="{{route('login')}}" role="button">Log In with Facebook</a></p>
        @endif
    </div>
@stop