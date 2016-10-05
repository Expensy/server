<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Confirm your account</title>

  <!-- Styles -->
  <style>
    html, body {
      background-color: #fff;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
      font-weight: 300;
      height: 100vh;
      margin: 0;
    }

    .flex-center {
      height: 100vh;
      align-items: center;
      display: flex;
      justify-content: center;
    }

    .content {
      text-align: center;
    }

    .title {
      font-size: 24px;
    }

    .success {
      color: #39b54a
    }

    .error {
      color: #ed1c24;
    }
  </style>
</head>
<body>
<div class="flex-center">
  <div class="content">
    <div class="title {{ $success ? 'success' : 'error' }}">
      @if ($success)
        You have correctly confirm your account!
      @else
        The token is not valid anymore.
      @endif
    </div>
  </div>
</div>
</body>
</html>
