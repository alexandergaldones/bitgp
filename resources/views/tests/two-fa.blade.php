<div>2-factor Authentication Val : {{ $two_factor_val }}</div>
<img src="{{ $google2fa_url }}" alt="">

{!! Form::open(['url' => 'google2fa/authenticate']) !!}
<input name="one_time_password" type="text">

<button type="submit">Authenticate</button>

@if(isset($message))
    <div>
        <label>{{$message}}</label>
    </div>
@endif

{!! Form::close() !!}