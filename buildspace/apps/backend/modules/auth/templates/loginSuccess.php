<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Sign in</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: system-ui, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: #1f1f1f;
            padding: 24px 28px;
            border-radius: 8px;
            width: 320px;
            box-shadow: 0 20px 40px rgba(0,0,0,.6);
        }
        label {
            display:block;
            font-size: 13px;
            margin-bottom: 4px;
            color:#aaa;
        }
        input {
            width:100%;
            padding:10px 12px;
            border-radius:4px;
            border:1px solid #444;
            background:#2a2a2a;
            color:#fff;
            font-size:14px;
            margin-bottom:16px;
        }
        button {
            width:100%;
            padding:10px 12px;
            border-radius:4px;
            border:none;
            background:#3b82f6;
            color:#fff;
            font-size:14px;
            font-weight:600;
            cursor:pointer;
        }
        .error {
            background:#5a1a1a;
            color:#ffbdbd;
            padding:8px 10px;
            border-radius:4px;
            font-size:13px;
            margin-bottom:16px;
        }
        h1 {
            color:#fff;
            font-size:16px;
            text-align:center;
            margin:0 0 20px;
            font-weight:600;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Sign in</h1>

        <?php if ($sf_user->hasFlash('error')): ?>
            <div class="error">
                <?php echo $sf_user->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo url_for('auth_do_login') ?>" method="POST">
            <div>
                <label>Username</label>
                <input type="text" name="username" autocomplete="username" />
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" />
            </div>

            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>
