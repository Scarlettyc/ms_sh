@extends('layouts.app')
@section('content')
<div class="container">
<script type="text/javascript">
var wsl= 'ws://127.0.0.1:6385'
ws = new WebSocket(wsl);//新建立一个连接
//如下指定事件处理 
ws.onopen = function(){ws.send('Test!'); };  
ws.onmessage = function(evt){console.log(evt.data);/*ws.close();*/};  
ws.onclose = function(evt){console.log('WebSocketClosed!');};  
ws.onerror = function(evt){console.log('WebSocketError!');}; 

</script>
</div>
@endsection
