@extends('layouts.layout',['title'=>'Welcome'])
@section('body')
    <div class="page-header">
        <h3>Profile</h3>
    </div>
    <div class="well">
        @if(isset($facebookUser))
            <div class="media">
                <div class="media-left">
                    <img alt="64x64" class="media-object"
                         data-src="holder.js/64x64"
                         style="width: 64px; height: 64px;"
                         src="{{ $facebookUser->getProfileUrl() }}"
                         data-holder-rendered="true">
                </div>
                <div class="media-right">
                    <h1>{{ $facebookUser->getFullName() }}</h1>
                </div>
            </div>
        @endif
    </div>
@stop