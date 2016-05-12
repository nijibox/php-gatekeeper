@extends('register.layout')


@section('content')
<p>ssh接続のアクセス許可を行うIPアドレスを登録します。</p>
<p>次のIPアドレスでよろしければ、登録をクリックしてください。</p>
<form method="post">
    登録するIPアドレス： {{ $userGIP }}
    <input type="hidden" name="addr" value="{{ $userGIP }}"><br/>
    <input type="submit" value="　登録　">
</form>
@endsection