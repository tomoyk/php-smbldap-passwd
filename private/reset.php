<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>実行：パスワード変更</title>
</head>
<body>
<?php

/* === NOTE ===========================================================================

 +++ Install Basic auth with LDAP +++
 yum -y install mod_authz_ldap
 vi /etc/httpd/conf.d/authz_ldap.conf
 mkdir /var/www/html/private 

 +++ Comment out this on /etc/sodoers +++
 # Defaults    requiretty

 +++ Add this on /etc/sudoers +++
 apache   ALL=(ALL)   NOPASSWD: /bin/sh, /bin/echo, /usr/local/sbin/smbldap-passwd

==================================================================================== */

function main(){
  
  // block CSRF
  if( $_SERVER['HTTP_REFERER'] != "http://192.168.0.50/private/") {
    echo "<h1>エラー</h1>\n<p>不正なアクセスです。</p>\n";
    return;
  }

  // check password & username
  if( !isset($_POST['pwd_a']) || $_POST['pwd_a']=='' || !isset($_POST['pwd_b']) || $_POST['pwd_b']=='' ) {
    echo "<h1>エラー</h1>\n<p>パスワードが未入力か，不正なアクセスです</p>\n";
    return;
  } else if( !isset($_SERVER['REMOTE_USER']) || $_SERVER['REMOTE_USER']=='' ) {
    echo "<h1>エラー</h1>\n<p>ユーザ認証に問題が発生しているか，不正なアクセスです</p>\n";
    return;
  }

  // get username
  $username = $_SERVER['REMOTE_USER'];

  // get password
  $passwd_a = $_POST['pwd_a'];
  $passwd_b = $_POST['pwd_b'];

  // check same password
  if($passwd_a != $passwd_b) {
    echo "<h1>エラー</h1>\n<p>新しいパスワードが一致しません</p>\n";
    return;
  }

  // check contain username
  if( stristr($passwd_a, $username) ){
    echo "<h1>エラー</h1>\n<p>パスワードにユーザー名を含むことはできません</p>\n";
    return;
  }

  // check password syntax & length
  if( !preg_match("/^(?=.*[0-9])(?=.*[a-z])[0-9a-zA-Z_\.\-]{6,20}$/",$passwd_a) ){
    echo "<h1>エラー</h1>\n<p>パスワードは6文字以上，20文字以下の半角英数字(混在)と記号( .-_ )で指定してください</p>\n";
    return;
  }

  // make command
  $cmd = "sudo /bin/sh -c \"/bin/echo -n $passwd_a | /usr/local/sbin/smbldap-passwd -p $username\" ";
  // debug code
  // echo $cmd."\n<br>";

  // submit query
  $result = shell_exec($cmd);
  // debug code
  // echo $result;
  
  // echo "<h1>パスワード変更完了</h1>\n<p>パスワードの変更が完了しました。トップに移動します</p>";
  // echo "<script>location.href=\"http://192.168.0.50/\"; </script>";

  header("Location:../end.html"); 
  exit();
}

main();

?>
</body>
</html>
