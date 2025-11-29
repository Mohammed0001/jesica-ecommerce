<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subscriber->name ?? 'Subscriber' }} - Newsletter</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;color:#222;}</style>
 </head>
 <body>
    <div style="max-width:600px;margin:auto;">
        {!! $bodyHtml !!}

        <hr />
        <p style="font-size:12px;color:#666">If you wish to unsubscribe, click <a href="{{ url('/newsletter/unsubscribe/' . ($subscriber->id ?? '') . '/' . ($subscriber->unsubscribe_token ?? '') ) }}">here</a>.</p>
    </div>
 </body>
</html>
