 <h1>Hello {{ session()->get('userName') }}</h1>
 <p>EC2 ID: {{ $instanceId }}</p>
 <p>You have visited this page {{$accessCount}} times</p>
 <a href="{{ route('logout') }}">Logout</a>
