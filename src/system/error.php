<!DOCTYPE html>
<html>
<head>
    <title>404 - Page not found</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
        body { text-align: center; padding: 10%; font: 20px Helvetica, sans-serif; color: #333; }
        h1 { font-size: 50px; margin: 0; }
        article { display: block; text-align: left; max-width: 650px; margin: 0 auto; }
        @media only screen and (max-width : 480px) {
            h1 { font-size: 40px; }
        }
    </style>
</head>
<body>
<article>
    <h1>404 - Page not found</h1>
    <p>The file <mark><?= $requestedFile; ?>.php</mark> you are trying to reach does not exist.</p>
    <p>You need to create one of the following file :</p>
    <ul>
        <li><?= $requestedFile ?>/index.php</li>
        <li><?= $requestedFile ?>.php</li>
    </ul>
</article>
</body>
</html>