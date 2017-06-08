@extends('layouts.page',[
    'DefaultVariables' => 'ToPassIntoTemplate',
    'title' => empty($title) ? '' : $title
])