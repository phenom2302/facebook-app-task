<div class="header clearfix">
    <nav>
        <ul class="nav nav-pills pull-right">
            <li role="presentation"><a href="{{route('homepage')}}">Home</a></li>
            @if(session(\App\User::USER_ACCESS_TOKEN_SESSION_NAME))
                <li role="presentation"><a href="{{route('profile')}}">Profile</a></li>
                <li role="presentation"><a class="btn btn-danger" href="{{route('logout')}}" role="button">Logout</a></li>
            @else
                <li role="presentation"><a class="btn btn-success" href="{{route('login')}}" role="button">Login</a></li>
            @endif

        </ul>
    </nav>
    <h3 class="text-muted">Facebook Test App</h3>
</div>