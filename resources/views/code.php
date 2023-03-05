<?php
@session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './src/Exception.php';
require './src/PHPMailer.php';
require './src/SMTP.php';
//get helper components
//include 'dbController.php';

include_once 'funct.php';
//include_once 'alert.php';
require_once 'tables.php';

$controller = new dbController();
$controllers = $controller->connect();
//$alert = new Alert();
$funct = new funct();
//$username = $_SESSION['username'];
$today = date('Y-m-d', time());

$server = "http://abamade.com.ng";

if(isset($_POST['placeOrder'])){
  /// shipping detail

  $session_id = session_id();
  $amt = $_POST['placeOrder'];
  $mode = $_POST['payment_mode'];
  $name = $_POST['lname']." ".$_POST['fname'];
  $phone = $_POST['phone'];
  $addr = $_POST['addr'];
  $city = $_POST['city'];
  $state = $_POST['state'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $courier = $_POST['courier'];
  $user_id = $_SESSION['user_id'];
  
  //$user_id = 12;
  $_SESSION['customer_name'] = $_POST['lname'];
  $_SESSION['orderNO'] = substr(md5(time()), 1, 10);

  $ship_array = array(0=>$courier, 1=>$state, 2=>$city);

  

  

  


  //create Order table
  $cols = array("order_no, user_id, amount, courier_id");
  $values = array("'{$_SESSION['orderNO']}' , '{$user_id}', '{$amt}', '{$courier}'");
  
  $_SESSION['order_Id'] = $funct->doCreateOrder($cols, $values);
  $orderDetail = $funct->doCreateOdetail(session_id(), $_SESSION['order_Id'], $ship_array);

  //create shipment table
  $s_cols = array('user_id', 'order_id', 'address', 'city', 'state', 'phone', 'recipient');
  $s_values = array("'$user_id'", "'{$_SESSION['order_Id']}'", "'$addr'", "'$city'", "'$state'", "'$phone'", "'$name'");
  $_SESSION['ship'] = $controller->insert(SHIPMENT, $s_cols, $s_values);


  //check for payment method
  if($mode == 'paystack'){
    $_SESSION['orderAmt'] = $amt;
    $_SESSION['email'] = $email;
    header("Location: ./ppay/index.php");
    exit;
  }
  //payment on cash on delivery
  else{
        
      //create payment
      $p_cols = array('trxtRef', 'user_id', 'amount', 'source', 'order_id', 'pay_status');
      $p_vals = array("'{$_SESSION['orderNO']}'","'$user_id'", "'$amt'","'Cash on Delivery'", "'{$_SESSION['order_Id']}'", "'0'");
      $payment = $controller->insert(PAYMENT, $p_cols, $p_vals);



      $where = "id ='".$_SESSION['order_Id']."'";
      $set = "payment_id ='".$payment."', ship_id='".$_SESSION['ship']."'";

      $updateOrder = $funct->doUpdateOrder(1, $payment, $_SESSION['ship'], $_SESSION['order_Id']);

      //retrieve order detail
      $_SESSION['orDetail'] = $funct->viewOrderDetail($_SESSION['order_Id']);

      // Order Address array
      $_SESSION['shipDetail'] = $funct->viewShipment($_SESSION['ship']);
     
      //delete Ordered items from cart 
      $del_items = $funct->doDeleteOItemInCart($_SESSION['order_Id'], session_id());

      // retrieve Order detail in table format
      $darts = $funct->orderContents($_SESSION['order_Id']);

      $message = '<html>
                      <head>
                        <title>Mail from Louis Chambers</title>
                          <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

                          <style>
                              body{
                                  font-family: "open sans", "Helvetica", "Arial", sans-serif;
                              }
                              .set_table tr th{
                                  /*padding: 5px 0;*/
                                  width: 30%;
                                  text-align: left;
                              }
                              table {
                                  border: 1px solid #ddd;
                                  width: 100%;
                                  margin-bottom: 30px;
                              }
                              th, td {
                                  padding: 15px;
                                  text-align: left;
                              }
                              tr:nth-child(even) {background-color: #f2f2f2;}
                          </style>
                      </head>
                      <body>
                        <img src="'.$server.'/assets/images/logo.png" style="width: 200px; margin-top: 10px"/>
                        <p style="margin-top: 30px">Date: '.$today.'</p>
                        <p>
                            Dear: <strong>'.$_SESSION['customer_name'].'</strong>;
                        </p>
                        <p>
                            Thank you for shopping with us. Your Order Number:'.$_SESSION['orderNO'].' and order details are as follows:
                        </p>
                        <p>
                          <table class="table table-striped">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th>Item(s)</th>
                                  <th>Unit Price</th>
                                  <th>Qty</th>
                                  <th>Amount</th>
                                </tr>
                              </thead>
                              <tbody>
                              '.$darts.'
                              <tr>
                                <td>Amount Paid</td>
                                <td>Mode of Payment</td>
                                <td colspan="3">'.$amt.'</td>
                              </tbody>
                            </table>
                        </p>
                        <p>
                            The above item(s) will be delivered to:
                            Name: '.$_SESSION['customer_name'].'  Phone: '.$_SESSION['shipDetail']->phone.' <br />
                            Address: '.$_SESSION['shipDetail']->address.'  '.$_SESSION['shipDetail']->city.' '.$_SESSION['shipDetail']->state.'
                        </p>   
                        <p>Thank You for your penthronage</p>
                      </body>
                    </html>';

          $attachment = "";
          require('./PEAR/Mail.php');

          $recipients = $_SESSION['email'];
          $headers['From'] = 'info@abamade.com.ng/';
          $headers['To'] = $recipients;
          $headers['Reply-To'] = $recipients;
          $headers['Subject'] = "Order Placed Successful";
          $headers['Content-Type'] = "text/html; charset=iso-8859-1";
          $headers['MIME-Version'] = "1.0";

          $body = $message;
          $params['sendmail_path'] = '/usr/lib/sendmail';

          // Create the mail object using the Mail::factory method
          $mail_object =& Mail::factory('sendmail', $params);

          $mail_object->send($recipients, $headers, $body); //customer

      header('Location: ../success.php?order='.$_SESSION['orderNO'] );
      exit();

  }



}

if(isset($_GET['gooreg'])){
    $p = $_GET['gooreg'];
    if($p == "true"){
        $user = substr($_GET['id'],0,8);
        $firstname = $_GET['fname'];
        $lastname = $_GET['sname'];
        $phone = "";
        $email = $_GET['eml'];
        $pwd = "";
        
        if($user == "" || $firstname  == "" || $lastname  == "" || $email == "" ){
            echo "error0";
        }else{
            if($funct->viewUserby("email", $email)) return "exist.";
            else{
                $aa = array("email, username, password, verification_status");
                $bb = array("'$email'", "'$user'", "'$pwd'", "'1'");   
                $result = $controller->insert(USERS, $aa, $bb);
                $a = array("user_id, f_name, phone");
                $b = array("'$result'", "'$firstname $lastname'", "'$phone'");
                $res = $controller->insert(CUSTOMERS, $a, $b);
                
                $a1 = array("user, fname, lname, phone, email");
                $b1 = array("'$result'", "'$firstname'", "'$lastname'", "'$phone'","'$email'");
                $res1 = $controller->insert("member", $a1, $b1);
                
                if($res1){
                  echo "true";
                  $_SESSION['rtl'] = "true";
                } 
                else echo "error1";  
              }
        }
    }  else echo "error2";
    //php/forms.php?gooreg=true&fname=${fname}&sname=${sname}&eml=${eml}&id=${id}
}

if(isset($_POST['registrationBn'])){
  $user = $_POST['firstname'];
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $pwd = md5($_POST['pwd']);
  $isPwd = $funct->validatePassword($_POST['pwd'],$_POST['cpwd']);
  $error = 0; $result = ""; 
  $msg = [];
  $isEmp = $funct->emptyValues($user, $firstname, $lastname, $phone, $email, $_POST['pwd']);
//   if($funct->viewUserby("username", $user)){
//     $msg['usn'] = "Username already taken.";
//     $error += 1;
//   }
  
  if($funct->viewUserby("email", $email)){
    $msg['eml'] = "Email already exist.";
    $error += 1;
  }

  if($isPwd){
    $msg['pwd'] = $isPwd;
    $error += 1;
  }
  
  if($error == 0){ 
    $aa = array("email, username, password");
    $bb = array("'$email'", "'$user'", "'$pwd'");   
    $result = $controller->insert(USERS, $aa, $bb);
    $a = array("user_id, f_name, phone");
    $b = array("'$result'", "'$firstname $lastname'", "'$phone'");
    $res = $controller->insert(CUSTOMERS, $a, $b);
    
    $a1 = array("user, fname, lname, phone, email");
    $b1 = array("'$result'", "'$firstname'", "'$lastname'", "'$phone'","'$email'");
    $res1 = $controller->insert("member", $a1, $b1);
    
    $mgs = sendRegMsg($result, $email, $controller, $server);
    if($mgs == "1") header('Location: ../verify.php');
    else {
        $msg['pwd'] = "Something went wrong, try again!";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
  }else{ 
     $_SESSION['msg'] = $msg; 
     $_SESSION['ret'] = ['user'=>$user, 'firstname'=>$firstname, 'lastname'=>$lastname, 'phone'=>$phone, 'email'=>$email]; 
     $_SESSION['emp']= $isEmp;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
  exit(); 
}

if(isset($_GET['regmsg'])){
  $user = $_GET['csrf'];
  $email = $_GET['token'];

  $mgs = sendRegMsg($user, $email, $controller, $server);
  if($mgs != "1"){
       $_SESSION['msg'] =  ['text-danger',$email,"Try again"];
       $_SESSION['data'] = ['otp', $user, $email];
  } 
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  
}

function sendRegMsg($id, $email, $controller, $server){
  $aaa = array("user, code");
  $chars = "1234567890";
  $code = substr(str_shuffle($chars),0,4);
  $codes = str_split($code, 1);
  $bbb = array("'$id'", "'$code'");
  $insert = $controller->insert(VERIFY, $aaa, $bbb);
  $_SESSION['msg'] = ['text-success',$email,"Enter the verification code"]; 
  $_SESSION['data'] = ['otp', $id, $email];
  $message = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body style="margin:0; padding: 0; box-sizing: border-box; background-color: #fff; font-family: sans-serif;">
  <div id="wrapper" style="max-width: 90%; margin: 40px auto;">
    <header>
        <center>
            <img src="'.$server.'/assets/images/logo.png" style="height:50px" alt="">
        </center>
    </header>
    <div class="first" style="background-color:#EFE9F5; padding:40px 0; margin:20px 0">
        
    </div>
    <div class="first">
      <h2 style="font-size:30px;text-align: center; color:#5E43EB">Security Code for Password Reset</h2>
      <p style="color: #111; font-size:20px; text-align: center;">Below is your one time password that you need to use to complete your authentication. The verification code will be valid for 60 seconds. Please do not share this code with anyone.</p>
      <center>
        <div style="background-color:#EFE9F5; width:200px; padding:20px 60px;font-weight:bold; font-size:30px;">
            <span>'.$codes[0].'</span>
            <span>'.$codes[1].'</span>
            <span>'.$codes[2].'</span>
            <span>'.$codes[3].'</span>
        </div>
      </center>
    </div>
    <div class="first" style="background-color:#EFE9F5; padding:40px 0; margin:20px 0">

    </div>
    <div class="third" style="margin-top: 50px;">
        <center>
            <div>
                <img src="'.$server.'assets/images/logo.png" style="height: 30px;" alt="">
            </div>
        </center>
      <p style="text-align: center;">&copy; Abamade 2023.All Rights Reserved</p>
      <p style="text-align: center;">Consumers are advised to read the <a href="'.$server.'policy.php">Terms & Conditions Carefully</a></p>
    </div>
  </div>
</body>
</html>';

      $from ="info@abamade.com.ng";
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
      $headers .= 'From: '.$from."\r\n".
      'Reply-To: '.$from."\r\n" .
      'X-Mailer: PHP/' . phpversion();             
      
      $mail = new PHPMailer(true);
          try {
              
              $mail->setFrom($from, "Aba Made");
              $mail->addAddress($email);     
              $mail->addReplyTo($from);
              $mail->isHTML(true); 
              $mail->SMTPSecure = 'ssl';
              $mail->Port = 25;
              $mail->Subject = "Registration Successful";
              $mail->Body    = $message;
              $mail->AltBody = $message;
              
            //   $mail->isSMTP();                                      
            // //   $mail->SMTPDebug = 1;  
            //   $mail->SMTPAuth = false;
            //   $mail->SMTPSecure = 'ssl';
            //   $mail->Host = "localhost"; 
            //   $mail->Port = 25;
            //   $mail->IsHTML(true); 

              $mail->send();
          $result = 1;
          } catch (Exception $e) {
              echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
              $result = 0;
          }
          return $result;
}


if(isset($_POST['resetBtn'])){
  $pwd = md5($_POST['pwd']);
  $eml = $_POST['token'];
  $user = $_POST['csrf'];
  $isPwd = $funct->validatePassword($_POST['pwd'],$_POST['cpwd']);
  $isEmp = ($_POST['pwd'] == "" || empty($_POST['pwd']))? "Enter a password" : false;

  if($isPwd || $isEmp){ //to oauth
    $msg = ($isEmp)? $isEmp : $isPwd;
    $_SESSION['data'] = ['success', $user, $eml];
    $_SESSION['msg'] = ['text-danger',$eml,$msg];
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }else{
    $res = $funct->updatePsw(USERS, 'password', $pwd, "id = '{$user}'");
    if($res){
      $_SESSION['msg'] = "success";
      $funct->deletePsw($user);
    }else $_SESSION['msg'] = "error";
    // header('Location: ../oauth.php');
    header('Location: ../login.php');
    exit(); 
  } 
}

if(isset($_POST['forgotBtn']) || isset($_GET['resetpwd'])){
  if(isset($_POST['forgotBtn'])) $user = $_POST['user']; 
  else $user = $_GET['csrf']; 
  if($funct->viewEmail($user)){
    if($funct->viewPwd($user)){
        $_SESSION['msg'] = "Log in with Google instead"; 
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }else{
        $res = $funct->viewEmail($user);
        $eml = $res->email;
        $id = $res->id;
        $aaa = array("user, code");
        $chars = "0123456789";
        $code = substr(str_shuffle($chars),0,4);
        $codes = str_split($code, 1);
        $bbb = array("'$id'", "'$code'");
        $insert = $controller->insert(VERIFY, $aaa, $bbb);
        $_SESSION['msg'] = ['text-success',$eml,"Please enter the code sent to your email"];
        $_SESSION['data'] = ['otp', $res->id, $user];
    
        $message = '<!DOCTYPE html>
          <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Document</title>
        </head>
        <body style="margin:0; padding: 0; box-sizing: border-box; background-color: #fff; font-family: sans-serif;">
          <div id="wrapper" style="max-width: 90%; margin: 40px auto;">
            <header>
                <center>
                    <img src="'.$server.'/assets/images/logo.png" style="height:50px" alt="">
                </center>
            </header>
            <div class="first" style="background-color:#EFE9F5; padding:40px 0; margin:20px 0">
                
            </div>
            <div class="first">
              <h2 style="font-size:30px;text-align: center; color:#5E43EB">Security Code for Password Reset</h2>
              <p style="color: #111; font-size:20px; text-align: center;">Below is your one time password that you need to use to complete your authentication. The verification code will be valid for 60 seconds. Please do not share this code with anyone.</p>
              <center>
                <div style="background-color:#EFE9F5; width:200px; padding:20px 60px;font-weight:bold; font-size:30px;">
                    <span>'.$codes[0].'</span>
                    <span>'.$codes[1].'</span>
                    <span>'.$codes[2].'</span>
                    <span>'.$codes[3].'</span>
                </div>
              </center>
            </div>
            <div class="first" style="background-color:#EFE9F5; padding:40px 0; margin:20px 0">
        
            </div>
            <div class="third" style="margin-top: 50px;">
                <center>
                    <div>
                        <img src="'.$server.'assets/images/logo.png" style="height: 30px;" alt="">
                    </div>
                </center>
              <p style="text-align: center;">&copy; Abamade 2023.All Rights Reserved</p>
              <p style="text-align: center;">Consumers are advised to read the <a href="'.$server.'policy.php">Terms & Conditions Carefully</a></p>
            </div>
          </div>
        </body>
        </html>';
    
          $from ="info@abamade.com.ng";
          $headers  = 'MIME-Version: 1.0' . "\r\n";
          $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
          $headers .= 'From: '.$from."\r\n".
          'Reply-To: '.$from."\r\n" .
          'X-Mailer: PHP/' . phpversion();             
          
          $mail = new PHPMailer(true);
              try {
                  
                  $mail->setFrom($from, "Aba Made");
                  $mail->addAddress($eml);     
                  $mail->addReplyTo($from);
                  $mail->isHTML(true); 
                  $mail->Subject = "Reset Password";
                  $mail->Body    = $message;
                  $mail->AltBody = $message;
        
                  $mail->send();
              $result = 1;
              } catch (Exception $e) {
                  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
              }
    
        header('Location: ../reset.php');
    }
  }else{
    $_SESSION['msg'] = "User account not found"; 
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
}

if(isset($_POST['otpBtn']) || isset($_GET['bypresbtn'])){
  if(isset($_POST['otpBtn'])){
      $code = $_POST['otp1'];
      $code .= $_POST['otp2'];
      $code .= $_POST['otp3'];
      $code .= $_POST['otp4'];
      $user = $_POST['token'];
      $id = $_POST['csrf'];
  }else {
      $code = $_GET['code'];
      $id = $_GET['csrf'];
  }
  
  $verify = $funct->getVerify($code,$id);
  if($verify){
    $_SESSION['msg'] = ['text-success',$email,"Enter a new password"];
    $_SESSION['data'] = ['success', $verify->user, $user];
  } else { 
   $_SESSION['msg'] =  ['text-danger',$user,"Incorrect OTP Code"];
   $_SESSION['data'] = ['otp', $id, $user];
  }
 header('Location: ' . $_SERVER['HTTP_REFERER']);
}

if(isset($_POST['regBtn']) || isset($_GET['bypregbtn'])){
  if(isset($_POST['regBtn'])){
      $code = $_POST['otp1'];
      $code .= $_POST['otp2'];
      $code .= $_POST['otp3'];
      $code .= $_POST['otp4'];
      $user = $_POST['token']; //email
      $id = $_POST['csrf'];
  }else {
      $code = $_GET['code'];
      $id = $_GET['csrf'];
  }
  
  $verify = $funct->getVerify($code,$id);
  if($verify){
    $delete = $funct->deletePsw($id);
    $update = $funct->updatePsw(USERS, 'verification_status', '1', "id = '{$id}'");
    if($delete && $update){
      $_SESSION['msg'] = "verified";
    }else{
      $_SESSION['msg'] = "unverified";
    }
    //header('Location: ../oauth.php');
    header('Location: ../login.php');
  }else{  
   $_SESSION['msg'] =  ['text-danger',$user,"Code is incorrect"];
   $_SESSION['data'] = ['otp', $id, $user];
   header('Location: ' . $_SERVER['HTTP_REFERER']);
  }   
}

if(isset($_POST['loginBtn'])){
  $page = (! empty($_POST['prevPage']) ) ? "../checkout.php" : "../index.php";
  $user = $_POST['username'];
  $pwd = md5($_POST['password']);
  if($funct->viewUser($user, $pwd)[0]){
    $user= $funct->viewUser($user, $pwd)[0];
    $cust = $funct->viewCustomer($user->id);
    $mem = $funct->viewMember($user->id);
    // Set login Session Parameters
    $_SESSION['loginset'] = TRUE;
    $_SESSION['user_id'] = $user->id;
    $_SESSION['username'] = $mem->fname;
    $_SESSION['email'] = $user->email;
    $_SESSION['role'] = $user->role_level;
    $_SESSION['customer_id'] = $cust->id;
    $_SESSION['fullname'] = $cust->f_name;
    $_SESSION['addr'] = $cust->address;
    $_SESSION['city'] = $cust->city;
    $_SESSION['state'] = $cust->state;
    $_SESSION['phone'] = $cust->phone;
    
    header('Location: ../index.php');
  }
  else{ 
    $error = ($funct->viewUser($user, $pwd)[1] == "TRUE")? ['pwd'=>"Incorrect username or password"] : ['usn'=>"User account does not exist"]; //;
    $error = ($funct->viewUser($user, $pwd)[1] == "VRFY")? ['vrf'=>"Account not verified"] : $error;
    $_SESSION['msg'] = $error;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }

  exit();
}

if(isset($_GET['goolgn'])){
    $p = $_GET['goolgn'];
    if($p == "true"){
        $usn = substr($_GET['id'],0,8);
        $email = $_GET['eml'];
        
        if($funct->viewPwdId($usn, $email)){
            $user = $funct->viewPwdId($usn, $email);
            $cust = $funct->viewCustomer($user->id);
            
            // Set login Session Parameters
            $_SESSION['loginset'] = TRUE;
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['role'] = $user->role_level;
            $_SESSION['customer_id'] = $cust->id;
            $_SESSION['fullname'] = $cust->f_name;
            $_SESSION['addr'] = $cust->address;
            $_SESSION['city'] = $cust->city;
            $_SESSION['state'] = $cust->state;
            $_SESSION['phone'] = $cust->phone;
            
            echo "true";
        } else echo "exist";
    } else echo "error";
    exit();
}

if(isset($_POST['updateAddr'])){
  $addr = $_POST['address'];
  $city = $_POST['city'];
  $state = $_POST['state'];
  $phone = $_POST['phone'];

  $set = " address ='$addr', city = '$city', state='$state', phone='$phone' ";
  $where = "user_id ='".$_SESSION['user_id']."'";
  $edit = $controller->edit("customers", $set, $where);
  
  if($edit){
    $_SESSION['msg'] = $funct->alert("Update Successful", "success");
    //update session variables
    $_SESSION['addr'] = $addr;
    $_SESSION['city'] = $city;
    $_SESSION['state'] = $state;
    $_SESSION['phone'] = $phone;
    // redirect to page;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit(); 
  }
}

if(isset($_POST['updateAccount'])){
    $name = $_POST['fullname'];
    $pwd = $_POST['npwd'];
    $cpwd = $_POST['cnpwd'];
    $curr_pwd = $funct->viewPasword($_SESSION['user_id']);
    $where = "id ='".$_SESSION['user_id']."'";
    $where2 = "user_".$where;
    
    if($_SESSION['fullname'] != $name){
      $controller->update("customers", 'f_name', $name, $where2);
      $_SESSION['msg'] = $funct->alert("Name Updated Successful", "success");
      $_SESSION['fullname'] = $name;
    }
    if(isset($_POST['pwd']) && !empty($_POST['pwd'])){
      if(($curr_pwd == $_POST['pwd']) && ($pwd == $cpwd)){
        $update = $controller->update('users', 'password', $pwd, $where);
        $_SESSION['msg'] = $funct->alert("Update Successful", "success");
      }
      else{
        $_SESSION['msg'] = $funct->alert("Wrong Current Password !!!", "warning");
      }
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit(); 
}



//  Delete User account
if(isset($_POST['deleteAccount'])){
    $user_id = $_SESSION['user_id']; 
    $where = "user_id ='{$user_id}'";
    $where1 = "user ='{$user_id}'";
    $where2 = "id ='{$user_id}'";

    $acc = $controller->delete2("customers", $where);
    $acc1 = $controller->delete2("member", $where1);
    $acc2 = $controller->delete2("users", $where2);

    if($acc && $acc1 && $acc2){
      $_SESSION['msg'] = $funct->alert("Account Deleted Successful", "success");
      header("location: ../logout.php");
      exit;
    }
    exit;


}

if(isset($_GET['item'])  && isset($_GET['cart'])){
  $session = session_id();
  $item_id = $_GET['item'];
  $qty = $_GET['cart'];
 
  $result = $funct->addCart($session, $item_id, $qty);

  if($result){
    $_SESSION['msg'] = $funct->alert("Item(s) added to Cart Successfully", "success");
  }
  else{
    $_SESSION['msg'] = $funct->alert("Unable to add item", "warning");
  }
  
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit(); 
}

if(isset($_GET['wish'])  && isset($_GET['list'])){
  $session = session_id();
  $item_id = $_GET['wish'];
   
  $result = $funct->addWish($session, $item_id);

  if($result){
    $_SESSION['msg'] = $funct->alert("Item added to Wishlist successfully", "success");
  }
  else{
    $_SESSION['msg'] = $funct->alert("Item already on wishlist", "warning");
  }
  
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit(); 
}

if(isset($_POST['blogComments'])){
  $blog_id = $_POST['blogComments'];
  $email = $_POST['email'];
  $user_id = ($_SESSION['loginset']) ? $_SESSION['user_id'] : 0;
  $note = $_POST['comment'];
  $name = $_POST['fname'];
  $a = array("blog_id", "user_id", "fullname", "email", "comments");
  $b = array("'$blog_id'", "'$user_id'", "'$name'", "'$email'", "'$note'");

  $result = $controller->insert(COMMENTS, $a, $b);

  if($result) {
    $_SESSION['msg'] = $funct->alert("Comment Added Successfully", "success");
  }
  else{
    $_SESSION['msg'] = $funct->alert("Error Adding Comment", "warning");
  }
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit(); 
}

if(isset($_POST['updateCart'])){
  $item = $_POST['qty'];

  //print_r($item); exit;
  
  foreach($item as $id=>$qty){
    $item_id = $id;
    $nQty = $qty;
    $where = "id = '".$id."'";
    if($update = $controller->update(CART, 'qty', $nQty, $where)){
      $_SESSION['msg'] = $funct->alert("Cart UpdatedSuccessfully", "success");
    }
    
  }
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit(); 
  
}

if( ( isset($_POST) && isset($_GET['code']) ) || ( isset($_GET['code']) && isset($_GET['item']) ) )
{
    $code = $_GET['code'];

    
    //add to cart
    if($code=='1')
    {
          
      $session = session_id();
         $item = $_POST['item_id'];
         $qty = $_POST['qty'];
         $aa = array("session_id, prod_id, qty");
         $bb = array("'$session'", "'$item'", "'$qty'");   
         $result = $controller->insert(CART, $aa, $bb);
         $_SESSION['msg'] = $funct->alert("Item(s) added to Cart Successfully", "success");
         header('Location: ' . $_SERVER['HTTP_REFERER']);
         exit(); 
    }
    // delete from cart
    if($code == "2"){
      $session_id = session_id();
      $cart_id = $_GET['item'];
      $where = "id='".$cart_id."' AND session_id='".$session_id."'";
      $result = $controller->delete2(CART, $where);
      $_SESSION['msg'] = $funct->alert("Item removed from Cart Successfully", "success");
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit(); 
    }

    //delete from wish list
    if($code == "3"){
      $session_id = session_id();
      $cart_id = $_GET['item'];
      $where = "id='".$cart_id."' AND session_id='".$session_id."'";
      $result = $controller->delete2(WISH, $where);
      $_SESSION['msg'] = $funct->alert("Item removed from Wishlist Successfully", "success");
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit(); 
    }

    //clear cart
    if($code == "4"){
      $session_id = session_id();
      $where = "session_id='".$session_id."'";
      $result = $controller->delete2(CART, $where);
      $_SESSION['msg'] = $funct->alert("Cart Emptied Successfully", "success");
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit(); 
    }


    //Subscription
    if($code=='11')
    {
         $email = $_POST['email'];
         $aa = array("email");
         $bb = array("'$email'");   
         $result = $controller->insert(SUBS, $aa, $bb);
         
         if($result){
          $_SESSION['errMsg'] = $funct->alerts("Item  Added to Cart", "success" );
         }
         header('Location: ' . $_SERVER['HTTP_REFERER']);
         exit(); 
    }
    
    //Add Contact form
    elseif ($code=='2') {
       
        $name = addslashes($_POST['name']);  
        $email = addslashes($_POST['email']);
        $subject = addslashes($_POST['subject']);  
        $phone = addslashes($_POST['phone']);  
        $msg = addslashes($_POST['message']);
        
        $message = '<html>
          <head>
            <title>Mail from Louis Chambers</title>
              <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

              <style>
                  body{
                      font-family: "open sans", "Helvetica", "Arial", sans-serif;
                  }
                  .set_table tr th{
                      /*padding: 5px 0;*/
                      width: 30%;
                      text-align: left;
                  }
                  table {
                      border: 1px solid #ddd;
                      width: 100%;
                      margin-bottom: 30px;
                  }
                  th, td {
                      padding: 15px;
                      text-align: left;
                  }
                  tr:nth-child(even) {background-color: #f2f2f2;}

              </style>
          </head>
          <body>
            <img src="http://www.lawyerosanakposan.com/assets/images/learning_mind_c.png" style="width: 200px; margin-top: 10px"/>
            <p style="margin-top: 30px">Date: '.$today.'</p>
            <p>
                Sender: <strong>'.$name.'</strong><br>
            </p>
            <p>
                Subject: <strong>'.$subject.'</strong><br>
            </p>
            <p>
                Email: <strong>'.$email.'</strong><br>
            </p>
            <p>
                Phone: <strong>'.$phone.'</strong><br>
            </p>
            <p>
                Message: <strong>'.$msg.'</strong><br>
            </p>        
          </body>
        </html>';

           $attachment = "";
            require('./PEAR/Mail.php');

                    $recipients ="help@lawyerosanakposan.com";
                    $headers['From'] = 'noreply@lawyerosanakposan.com';
                    $headers['To'] = $recipients;
                    $headers['Reply-To'] = $recipients;
                    $headers['Subject'] = $type;
                    $headers['Content-Type'] = "text/html; charset=iso-8859-1";
                    $headers['MIME-Version'] = "1.0";

                    $body = $message;
                    $params['sendmail_path'] = '/usr/lib/sendmail';

                    // Create the mail object using the Mail::factory method
                    $mail_object =& Mail::factory('sendmail', $params);

                    $mail_object->send($recipients, $headers, $body);
                    $mail_object->send("wispm1@gmail.com", $headers, $body);

                  //MAIL METHOD 2
                /*  $alert->email($recipients, $type, $message, $attachment); 
                  $alert->email("wispm1@yahoo.com", "New Registration", $message, $attachment);*/

         
          $_SESSION['errMsg'] = "Contact message sent successfully";  
         header('Location: ' . $_SERVER['HTTP_REFERER']);

    }      
    
 //adding new game comments  
    elseif ($code =='3') {
      $name = $_POST['yname'];
      $email = $_POST['email'];
      $message = $_POST['message'];
      $id = $_POST['id'];

      $a = array("game", "user", "email", "comment");
      $b = array("'$id'", "'$name'", "'$email'", "'$message'");
      $result = $controller->insert(COMM, $a, $b);

          //add Log
              $descr = "New Comment Added for user: ".$name." and email: ".$email;
              $ijk = array("email", "descr");
              $kji = array("'$email'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = $descr;  
              header('Location: ' . $_SERVER['HTTP_REFERER']);
      
    }

    //Add Blog
    elseif ($code =='4') {
        $title = $_POST['title'];
        $descr = $_POST['descr'];
        $image = $_FILES['image']['name'];
      
        if(!empty($image))
        {
            $target_dir = "../../assets/media-demo/news/";
            $target_dir . basename( $_FILES["image"]["name"]);
              if (file_exists($target_dir . $_FILES["image"]["name"])) 
                {
                  $image = dechex(time()) . $_FILES['images']['name'];
                  move_uploaded_file($_FILES['image']['tmp_name'],
                  "../../assets/media-demo/news/" . $image);
                }
              else
                {            
                  move_uploaded_file($_FILES['image']['tmp_name'],
                  "../../assets/media-demo/news/" . $_FILES['image']['name']);
                }      
        }

        $a = array("title","descr", "image", "owner");
        $b = array("'$title'", "'$descr'", "'$image'", "'Admin'");
        $result = $controller->insert(NEWS, $a, $b);
        

        //add Log
              $descr = "A news item was added.";
              $ijk = array("email", "descr");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = $descr. " successfully";  
              header('Location: ' . $_SERVER['HTTP_REFERER']);   
    }

    //Add new slide
    elseif ($code =='5') {
        $text1 = addslashes($_POST['text1']);
        $text2 = addslashes($_POST['text2']);
        $amount = addslashes($_POST['amount']);
        $descr = $_POST['descr'];

      $pix = $_FILES['pix']['name'];

        if(!empty($pix))
        {
            $target_dir = "../../assets/media-demo/banner/";
            $target_dir . basename( $_FILES["pix"]["name"]);
              if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                {
                  $pix = dechex(time()) . $_FILES['pix']['name'];
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../../assets/media-demo/banner/" . $pix);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../../assets/media-demo/banner/" . $_FILES['pix']['name']);
                }  

                  //send to db
        $aa = array("text1", "text2", "pix", "amount", "descr", "status");
        $bb = array("'$text1'", "'$text2'", "'$pix'", "'$amount'", "'$descr'", "'0'");   
        $result = $controller->insert(SLIDES, $aa, $bb);

        //send notifcation and other messages
              $descr = "New Slide added.";
              $ijk = array("email", "descr");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = $descr;  
            header('Location: ' . $_SERVER['HTTP_REFERER']);           


        }else
        {
          $_SESSION['errMsg'] = "Please select an image to add";  
              header('Location: ' . $_SERVER['HTTP_REFERER']);        
        }
      
          
    }

    //add Testimony
    elseif ($code =='6') {
        $name = $_POST['name'];
        $descr = $_POST['descr'];
        $position = $_POST['position'];
        $image = $_FILES['pix']['name'];

          if(!empty($image))
        {
            $target_dir = "../../assets/media-demo/banner/";
            $target_dir . basename( $_FILES["pix"]["name"]);
              if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                {
                  $image = dechex(time()) . $_FILES['pix']['name'];
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../../assets/media-demo/banner/" . $image);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../../assets/media-demo/banner/" . $_FILES['pix']['name']);
                }      
        }
        

        //send to database (testimony table) 
        $aa = array("fullname", "testimony", "position", "pix");
        $bb = array("'$name'", "'$descr'", "'$position'", "'$image'");   
        $result = $controller->insert(TESTI, $aa, $bb);

      
        //send notifcation and other messages
              $descr = "New Tesimonial added";
              $ijk = array("email", "descr");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = $descr." successfully"; 
              header('Location: ' . $_SERVER['HTTP_REFERER']);     
    }

    //add staff member
    elseif ($code =='7') {
        $name = $_POST['name'];
        $descr = $_POST['descr'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $type = $_POST['type'];
        $image = $_FILES['pix']['name'];

          if(!empty($image))
        {
            $target_dir = "../assets/users/";
            $target_dir . basename( $_FILES["pix"]["name"]);
              if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                {
                  $image = dechex(time()) . $_FILES['pix']['name'];
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/users/" . $image);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/users/" . $_FILES['pix']['name']);
                }      
        }
        

        //send to database (member table) 
        $aa = array("fullname", "email", "phone", "summary", "pix", "type");
        $bb = array("'$name'", "'$email'", "'$phone'", "'$descr'", "'$image'", "'$type'");   
        $result = $controller->insert(PEOPLE, $aa, $bb);
        if($result)
        {
          $pass = dechex(time()); $passes = md5($pass); 
          $aa = array("username", "password", "status", "rank");
          $bb = array("'$email'", "'$passes'", "'0'", "'$type'");   
          $result = $controller->insert("access", $aa, $bb);
        }

      
        //send notifcation and other messages
              $descr = "New user added with password <b>" .$pass."</b>";
              $ijk = array("email", "descr");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = $descr." successfully"; 
              header('Location: ' . $_SERVER['HTTP_REFERER']);   
    }

    

    //Add new Report
      elseif ($code =='8') {
        $type = $_POST['type'];
        $title = $_POST['title'];
        $descr = $_POST['descr'];
        $email = $_SESSION['username'];
        $image = $_FILES['pix']['name'];

          if(!empty($image))
        {
            $target_dir = "../assets/attached-files/";
            $target_dir . basename( $_FILES["pix"]["name"]);
              if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                {
                  $image = dechex(time()) . $_FILES['pix']['name'];
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/attached-files/" . $image);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/attached-files/" . $_FILES['pix']['name']);
                }      
        }
        

        //send to database (report table) 
        $aa = array("sender", "type", "title", "descr", "attach");
        $bb = array("'$email'", "'$type'", "'$title'", "'$descr'", "'$image'");   
        $result = $controller->insert(REPT, $aa, $bb);
          
        //send notifcation and other messages
              $descr = "New report added";
              $ijk = array("email", "descr");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = $descr." successfully"; 
              header('Location: ' . $_SERVER['HTTP_REFERER']);

      }   


    //register esps
      elseif($code =='9') {
            $title = addslashes($_POST['title']);
            $name = addslashes($_POST['name']);  
            $email = addslashes($_POST['email']);
            $phone = addslashes($_POST['phone']);  
            $address = addslashes($_POST['address']);
            $image = $_FILES['pix']['name'];

            $pass = dechex(time());  

          if(!empty($image))
        {
            $target_dir = "../assets/images/users/";
            $target_dir . basename( $_FILES["pix"]["name"]);
              if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                {
                  $image = dechex(time()) . $_FILES['pix']['name'];
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/images/users/" . $image);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/images/users/" . $_FILES['pix']['name']);
                }      
        }

        //send to database (report table) 
        $aa = array("regCode", "title", "fullname", "email", "phone", "address", "pix", "regDate");
        $bb = array("'$pass'", "'$title'", "'$name'", "'$email'", "'$phone'", "'$address'", "'$image'", "'$today'");   
        $result = $controller->insert("esp", $aa, $bb);
            
            $message = '<html>
              <head>
                <title>Mail from RealNov8</title>
                  <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

                  <style>
                      body{
                          font-family: "open sans", "Helvetica", "Arial", sans-serif;
                      }
                      .set_table tr th{
                          /*padding: 5px 0;*/
                          width: 30%;
                          text-align: left;
                      }
                      table {
                          border: 1px solid #ddd;
                          width: 100%;
                          margin-bottom: 30px;
                      }
                      th, td {
                          padding: 15px;
                          text-align: left;
                      }
                      tr:nth-child(even) {background-color: #f2f2f2;}

                  </style>
              </head>
              <body>
                <img src="http://www.realnov8.com.ng/assets/img/logo.png" style="width: 200px; margin-top: 10px"/>
                <p style="margin-top: 30px">Date: '.$today.'</p>
                <p>
                    Dear: <strong>'.$name.'</strong><br>
                    Thank you for your interest in becoming an ESP. We look forward to a beautiful working relationship with you. Your ESP code is '.$pass.' which can be given to your other referrals or used when making transactions with us. Please keep it safe.
                </p>
                <p>
                  The RealNov8 Team.
                </p>
                
              </body>
            </html>';

              $attachment = "";
                require('./PEAR/Mail.php');

                        $recipients ="info@realnov8.com.ng";
                        $headers['From'] = 'noreply@realnov8.com.ng';
                        $headers['To'] = $recipients;
                        $headers['Reply-To'] = $recipients;
                        $headers['Subject'] = $type;
                        $headers['Content-Type'] = "text/html; charset=iso-8859-1";
                        $headers['MIME-Version'] = "1.0";

                        $body = $message;
                        $params['sendmail_path'] = '/usr/lib/sendmail';

                        // Create the mail object using the Mail::factory method
                        $mail_object =& Mail::factory('sendmail', $params);

                        $mail_object->send($recipients, $headers, $body);
                        $mail_object->send("wispm1@yahoo.com", $headers, $body);
            
              $_SESSION['errMsg'] = "Registration successful with ESP code ".$pass." please check your mail";  
            header('Location: ' . $_SERVER['HTTP_REFERER']);
      
    }

  //Add property GAllery
    elseif ($code =='10') {
      $pix1 = $_FILES['pix1']['name'];
      $pix2 = $_FILES['pix2']['name'];
      $pix3 = $_FILES['pix3']['name'];
      $prop = $_POST['prop'];



      if(!empty($pix1))
        {


            $target_dir = "../../assets/media-demo/properties/";
            $target_dir . basename( $_FILES["pix1"]["name"]);
              if (file_exists($target_dir . $_FILES["pix1"]["name"])) 
                {
                  $pix1 = dechex(time()) . $_FILES['pix1']['name'];
                  move_uploaded_file($_FILES['pix1']['tmp_name'],
                  "../../assets/media-demo/properties/" . $pix1);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix1']['tmp_name'],
                  "../../assets/media-demo/properties/" . $_FILES['pix1']['name']);
                }     

                //send to database (report table) 
                $aa = array("propid", "filename", "type");
                $bb = array("'$prop'", "'$pix1'", "'0'");   
                $result = $controller->insert("propgal", $aa, $bb);
            
        }

        if(!empty($pix2))
        {
            $target_dir = "../../assets/media-demo/properties/";
            $target_dir . basename( $_FILES["pix2"]["name"]);
              if (file_exists($target_dir . $_FILES["pix2"]["name"])) 
                {
                  $pix2 = dechex(time()) . $_FILES['pix2']['name'];
                  move_uploaded_file($_FILES['pix2']['tmp_name'],
                  "../../assets/media-demo/properties/" . $pix2);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix2']['tmp_name'],
                  "../../assets/media-demo/properties/" . $_FILES['pix2']['name']);
                }     

                //send to database (report table) 
                $aa = array("propid", "filename", "type");
                $bb = array("'$prop'", "'$pix2'", "'0'");   
                $result = $controller->insert("propgal", $aa, $bb);
            
        }

        if(!empty($pix3))
        {
            $target_dir = "../../assets/media-demo/properties/";
            $target_dir . basename( $_FILES["pix3"]["name"]);
              if (file_exists($target_dir . $_FILES["pix3"]["name"])) 
                {
                  $pix3 = dechex(time()) . $_FILES['pix3']['name'];
                  move_uploaded_file($_FILES['pix3']['tmp_name'],
                  "../../assets/media-demo/properties/" . $pix3);
                }
              else
                {            
                  move_uploaded_file($_FILES['pix3']['tmp_name'],
                  "../../assets/media-demo/properties/" . $_FILES['pix3']['name']);
                }     

                //send to database (report table) 
                $aa = array("propid", "filename", "type");
                $bb = array("'$prop'", "'$pix3'", "'0'");   
                $result = $controller->insert("propgal", $aa, $bb);
            
        }

        $descr = "Property gallery added.";
        $ijk = array("email", "descr");
        $kji = array("'$username'", "'$descr'");        
        $controller->insert(LOGS, $ijk, $kji);

      $_SESSION['errMsg'] = $descr; 
      header('Location: ' . $_SERVER['HTTP_REFERER']);   


    }
    //reciepts
    elseif($code=='11') {
            $payee = addslashes($_POST['payee']);
            $paidfor = addslashes($_POST['paidfor']);  
            $total = addslashes($_POST['total']);
            $amount = addslashes($_POST['amount']);  
            $phone = addslashes($_POST['phone']);
            $email = addslashes($_POST['email']);
            $bal = addslashes($_POST['balance']);
            //$bal = $total - $amount;
          

        //send to database (report table) 
        //$aa = array("regCode", "title", "fullname", "email", "phone", "address", "pix", "regDate");
        //$bb = array("'$pass'", "'$title'", "'$name'", "'$email'", "'$phone'", "'$address'", "'$image'", "'$today'");   
        //$result = $controller->insert("esp", $aa, $bb);
            
            $message = '<html>
              <head>
                <title>Mail from RealNov8</title>
                  <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

                  <style>
                      body{
                          font-family: "open sans", "Helvetica", "Arial", sans-serif;
                      }
                      .set_table tr th{
                          /*padding: 5px 0;*/
                          width: 30%;
                          text-align: left;
                      }
                      table {
                          border: 1px solid #ddd;
                          width: 100%;
                          margin-bottom: 30px;
                      }
                      th, td {
                          padding: 15px;
                          text-align: left;
                      }
                      tr:nth-child(even) {background-color: #f2f2f2;}

                  </style>
              </head>
              <body>
                <img src="http://www.realnov8.com.ng/assets/img/logo.png" style="width: 200px; margin-top: 10px"/>
                <h3>Receipt For Payment</h3>
                <p style="margin-top: 30px">Date: '.$today.'</p>

                <p>
                    Dear: <strong>'.$payee.'</strong><br>
                    Thank you for your payments, below are the details.
                    <table>
                      <tr>
                        <th> Paid For</th> <td> '.$paidfor.'</td>
                      </tr>
                      <tr>
                        <th> Total Amount</th> <td> '.$total.'</td>
                      </tr>
                      <tr>
                        <th> Amount Paid</th> <td> '.$amount.'</td>
                      </tr>
                      <tr>
                        <th> Balance</th> <td> '.$bal.'</td>
                      </tr>

                    </table>
                </p>
                <p>
                  The RealNov8 Team.
                </p>
              
              </body>
            </html>';

              $attachment = "";
                require('./PEAR/Mail.php');

                        $recipients ="info@realnov8.com.ng";
                        $headers['From'] = 'noreply@realnov8.com.ng';
                        $headers['To'] = $recipients;
                        $headers['Reply-To'] = $recipients;
                        $headers['Subject'] = $type;
                        $headers['Content-Type'] = "text/html; charset=iso-8859-1";
                        $headers['MIME-Version'] = "1.0";

                        $body = $message;
                        $params['sendmail_path'] = '/usr/lib/sendmail';

                        // Create the mail object using the Mail::factory method
                        $mail_object =& Mail::factory('sendmail', $params);

                        $mail_object->send($recipients, $headers, $body);
                        $mail_object->send($email, $headers, $body);
                        $mail_object->send("wispm1@yahoo.com", $headers, $body);
            
              $_SESSION['errMsg'] = "Receipt Sent for users with name ".$payee.".";  
            header('Location: ' . $_SERVER['HTTP_REFERER']);
                
    }
    elseif($code=='12') {
      //add noard content
      $pix = "";
      $title = addslashes($_POST['title']);
      $content = addslashes($_POST['content']);
      $pix = $_FILES['pix']['name'];
      if(!empty($pix))
                    {
                        $target_dir = "../assets/img/others/";
                        $target_dir . basename( $_FILES["pix"]["name"]);
                        if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                        {
                            $pix = dechex(time()) . $_FILES['pix']['name'];
                            move_uploaded_file($_FILES['pix']['tmp_name'],
                            "../assets/img/others/" . $pix);
                        }
                        else
                        {            
                            move_uploaded_file($_FILES['pix']['tmp_name'],
                            "../assets/img/others/" . $_FILES['pix']['name']);
                        }
                    }

      //send to database (documents table) 
        $aa = array("title", "content", "type", "attachment");
        $bb = array("'$title'", "'$content'", "'0'", "'$pix'");   
        $result = $controller->insert(DOCS, $aa, $bb);

        //send notifcation and other messages
              $descr = "New Board Information Sent.";
              $ijk = array("username", "activity");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = "New Board Content Added Successfully";  
              header('Location: ' . $_SERVER['HTTP_REFERER']); 
    }
    elseif($code=='13') {
      //add property cat
      $title = addslashes($_POST['title']);
      $content = addslashes($_POST['content']);

      //send to database (prp cats table) 
        $aa = array("title", "description");
        $bb = array("'$title'", "'$content'");   
        $result = $controller->insert(PROPCATS, $aa, $bb);

        //send notifcation and other messages
              $descr = "New Property Type Added.";
              $ijk = array("username", "activity");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = "New Property Type Added Successfully";  
              header('Location: ' . $_SERVER['HTTP_REFERER']); 
    }
    //moving request
    elseif($code=='14') {
      $tent = $_POST['tent'];
      $type = $_POST['type'];
      $dom = $_POST['dom'];
      
      //send to database (prp cats table) 
        $aa = array("tenant", "type", "dDate", "status", "addedBy", "description");
        $bb = array("'$tent'", "'$type'", "'$dom'", "'0'", "'$username'", "''");   
        $result = $controller->insert(MOVING, $aa, $bb);  

        //send notifcation and other messages
              $descr = "New Moving Request Added.";
              $ijk = array("username", "activity");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = "New Moving Request Added Successfully";  
              header('Location: ' . $_SERVER['HTTP_REFERER']); 
    }
    //owner and tenant requests
    elseif($code=='15') {

        $type = $_POST['type'];  $pix = "";
        $title = addslashes($_POST['title']);
        $content = addslashes($_POST['content']);
        $user = $_SESSION['user_id'];
        $pix = $_FILES['pix']['name'];
        if(!empty($pix))
          {
              $target_dir = "../assets/img/others/";
              $target_dir . basename( $_FILES["pix"]["name"]);
              if (file_exists($target_dir . $_FILES["pix"]["name"])) 
              {
                  $pix = dechex(time()) . $_FILES['pix']['name'];
                    move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/img/others/" . $pix);
              }
                else
              {            
                  move_uploaded_file($_FILES['pix']['tmp_name'],
                  "../assets/img/others/" . $_FILES['pix']['name']);
              }
          }
        
        //send to database (prp cats table) admin and staff
        if($_SESSION['role']==3)
        {
          //get staff assigned - what if there are multiple staffs attending to him?
        
        $where = "owner = $user";  $result = $controller->retrieve(PROPERTIES, $where);  
        if($result !=FALSE)
        {
          while ($row = mysqli_fetch_array($result)) {
            $propID = $row['id'];
            $wher = "propID = $propID"; $results = $controller->retrieve(PROPSTAFF, $wher); 
            if($results!=FALSE)
            {
              $rows = mysqli_fetch_array($results); extract($rows);
              $dStaff = $rows['staff'];

              $aa = array("sender", "recievers", "type", "actual", "content", "attachment", "status");
              $bb = array("'$username'", "'$dStaff'", "'99'", "'$type'", "'$content'", "'$pix'", "'0'");   
              $result = $controller->insert(INQUISITIONS, $aa, $bb); 

              $aa = array("user", "user2", "activity", "pageLink", "status");
              $bb = array("'$username'", "'$dStaff'", "'$title'", "'userRequests.php'", "'0'");   
              $result = $controller->insert(NOTIFICATIONS, $aa, $bb); 
            }
          }
        }
      }
      else{

      //get the property first
        $sas = "tenant = $user"; $ands = $controller->retrieve(PROPTENANTS, $sas); 
        if($ands !=FALSE)
        {
          while($dam = mysqli_fetch_array($ands))
          {
            extract($dam);
            $propID = $dam['property']; $sub = $dam['sub'];
            $wher = "propID = $propID"; $results = $controller->retrieve(PROPSTAFF, $wher); 
            if($results!=FALSE)
            {
              $rows = mysqli_fetch_array($results); extract($rows);
              $dStaff = $rows['staff'];

              $aa = array("sender", "recievers", "type", "actual", "content", "attachment", "status");
              $bb = array("'$username'", "'$dStaff'", "'99'", "'$type'", "'$content'", "'$pix'", "'0'");   
              $result = $controller->insert(INQUISITIONS, $aa, $bb); 

              $aa = array("user", "user2", "activity", "pageLink", "status");
              $bb = array("'$username'", "'$dStaff'", "'$title'", "'userRequests.php'", "'0'");   
              $result = $controller->insert(NOTIFICATIONS, $aa, $bb); 
            }
          }     
          }
        }
        
          $aa = array("user", "user2", "activity", "pageLink", "status");
          $bb = array("'$username'", "'1'", "'$title'", "'userRequests.php'", "'0'");   
          $result = $controller->insert(NOTIFICATIONS, $aa, $bb);
          
          //send notifcation and other messages
                $descr = "New Request Sent.";
                $ijk = array("username", "activity");
                $kji = array("'$username'", "'$descr'");        
                $controller->insert(LOGS, $ijk, $kji);

                $_SESSION['errMsg'] = "New Request Added Successfully";  
                header('Location: ' . $_SERVER['HTTP_REFERER']); 

    }
    //inspection feedback add
    elseif($code=='16') {
      $maxi = $_POST['maxi'];
      $dmax = $maxi+1; 
      $theinspect = $_POST['theinspect'];
    
      for ($i=1; $i < $dmax ; $i++) { 
      $var = $_POST[$i]; 

      //add to inventory feedback table
        $aa = array("inspectionId", "inventoryId", "remark", "who");
        $bb = array("'$theinspect'", "'$i'", "'$var'", "'$username'");   
        $result = $controller->insert(INSPECTFEED, $aa, $bb); 
      }

        //send notifcation and other messages
              $descr = "added an inventory based feedback";
              $ijk = array("username", "activity");
              $kji = array("'$username'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

      $_SESSION['errMsg'] = "Inventory feedback successful";  
      header('Location: ' . $_SERVER['HTTP_REFERER']);
    
    }
    elseif($code=='17'){
      //add commercial property

      $dUser = $_SESSION['user_id']; //the person adding
      $dUsername = $_SESSION['username']; //the person adding
      $userType = $_POST['userType']; //the entering cat
            
      $surname = addslashes($_POST['surname']);
      $othernames = addslashes($_POST['othername']);  $address = addslashes($_POST['address']);
      $phone = $_POST['phone'];  $email = $_POST['email'];
      $dob = $_POST['dob'];  $gender = $_POST['gender'];
      $pix = $_FILES['pix']['name'];  
          
      $today = date('Y-m-d', time());
      $pass = dechex(time()); $passes = md5($pass);        

            //validate phone and add bio to people table
                $wer = "phone = '$phone' || email = '$email'";
                $result = $controller->retrieve(PEOPLE, $wer);
                if ($result != FALSE) {
                  $_SESSION['errMsg'] = "A user with these details already exists";  
                  header('Location: ../index.php'); 
                }

            if(!empty($pix))
                    {
                        $target_dir = "../assets/img/others/";
                        $target_dir . basename( $_FILES["pix"]["name"]);
                        if (file_exists($target_dir . $_FILES["pix"]["name"])) 
                        {
                            $pix = dechex(time()) . $_FILES['pix']['name'];
                            move_uploaded_file($_FILES['pix']['tmp_name'],
                            "../assets/img/others/" . $pix);
                        }
                        else
                        {            
                            move_uploaded_file($_FILES['pix']['tmp_name'],
                            "../assets/img/others/" . $_FILES['pix']['name']);
                        }
                    }

            $aa = array("surname", "othernames", "gender", "email", "phone", "address", "dob", "type", "image", "regDate");
            $bb = array("'$surname'", "'$othernames'", "'$gender'", "'$email'", "'$phone'", "'$address'", "'$dob'", "'$userType'", "'$pix'", "'$today'");   
            $result = $controller->insert(PEOPLE, $aa, $bb);
            //Get the just inserted ID
            $wer = "phone = '$phone'";
            $last_rec = $controller->retrieve(PEOPLE, $wer); 
            $last_row = mysqli_fetch_array($last_rec); extract($last_row);
            $last_id = $last_row['id'];

            //add access table
            $aa = array("username", "password", "rank", "status", "accountStat");
            $bb = array("'$phone'", "'$passes'", "'$userType'", "'0'", "'0'");   
            $result = $controller->insert(LOGIN, $aa, $bb);


              //Commercial Tenant
              $propR = $_POST['propRequired']; $position = addslashes($_POST['position']); $bizadd = addslashes($_POST['bizadd']); $occupation = addslashes($_POST['biznature']); $proptype['proptype']; $puses=addslashes($_POST['puses']); $cbizadd = addslashes($_POST['cbizadd']); $dmi = $_POST['dmi']; $rfl = addslashes($_POST['rfl']);  $pdom = $_POST['pdom'];  $nationality = $_POST['nationality'];  $city = $_POST['city'];  $lga = addslashes($_POST['lga']); $xdate = $_POST['xdate'];

                $pow = addslashes($_POST['bizname']); $bizowner =addslashes($_POST['bizowner']); $witnes = addslashes($_POST['witnes']); $witph = addslashes($_POST['witph']);  $witad = addslashes($_POST['witad']); 

                $pref1 = addslashes($_POST['pref1']); $rel1 = addslashes($_POST['rel1']); $pref1p = addslashes($_POST['pref1p']);  $pref1a = addslashes($_POST['pref1a']); $pref2 = addslashes($_POST['pref2']); $rel2 = addslashes($_POST['rel2']); $pref2p = addslashes($_POST['pref2p']);  $pref2a = addslashes($_POST['pref2a']); $prref1 = addslashes($_POST['prref1']); $prref1p = addslashes($_POST['prref1p']);  $prref1a = addslashes($_POST['prref1a']);  $prref2 = addslashes($_POST['prref2']); $prref2p = addslashes($_POST['prref2p']);  $prref2a = addslashes($_POST['prref2a']); 


                $gaun1 = addslashes($_POST['gaun1']); $gaua1 = addslashes($_POST['gaua1']); $gaup1 = addslashes($_POST['gaup1']); $gaun2 = addslashes($_POST['gaun2']); $gaua2 = addslashes($_POST['gaua2']); $gaup2 = addslashes($_POST['gaup2']); 

                $agree = ""; $reof = addslashes($_POST['reof']); $oralInt = $_POST['oralInt']; $remarks = addslashes($_POST['remarks']); $premark=addslashes($_POST['premark']); $reasons2 = addslashes($_POST['reasons2']); 
                //$_POST['agree'];   

                  //insert into tenants
                $aa = array("userID", "currAddress", "dateEntered", "reasonsforleaving", "currType", "nationality", "state", "lga", "religion", "marital", "occupation", "otherIncome", "incomeRange", "placeofwork",  "position", "workPhone", "spouseName", "spouseOccup",  "spouseDob", "spousePOW", "spousePosition", "spousePhone", "children",  "otherWards", "nextOfKin", "kinEmail", "kinPhone", "dateAdded", "addedBy");
                $bb = array("'$last_id'", "'$bizadd'", "'$dmi'", "'$rfl'", "''", "'$nationality'", "'$city'", "'$lga'", "''", "''", "'$occupation'", "''", "''", "'$cbizadd'", "'$position'", "''", "''", "''", "''", "''", "''", "''", "''", "''", "''", "''", "''", "'$today'", "'$dUsername'");   
                $result = $controller->insert(TENANTS, $aa, $bb);

                //Get the just inserted tenant ID
                $wer = "userID = '$last_id'";
                $last_rec = $controller->retrieve(TENANTS, $wer); 
                $last_row = mysqli_fetch_array($last_rec); extract($last_row);
                $tenant_id = $last_row['id'];       

                //Add to commercial table
                  $aa = array("tenant", "bizname", "bizowner", "witnes", "witphone", "witadd");
                $bb = array("'$tenant_id'", "'$pow'", "'$bizowner'", "'$witnes'", "'$witph'", "'$witad'");   
                $result = $controller->insert(COMMERCIAL, $aa, $bb);

              
              //Add to rentals table
                $aa = array("tenantID", "peopleID", "accoType", "quantity", "proposedAddress", "proposedDate", "noOfPersons", "payResponsibility", "empFile", "dateSubmitted", "receivingOff", "addedBy", "remarks", "oralInterview",  "remark2", "status", "reasons");
                $bb = array("'$tenant_id'", "'$last_id'", "'$proptype'", "'1'", "'$propR'", "'$pdom'", "''", "'Self'", "''", "'$xdate'", "'$reof'", "'$dUsername'", "'$remarks'", "'$oralInt'", "''", "'$premark'", "'$reasons2'");   
                $result = $controller->insert(RENTALS, $aa, $bb);

                //gaurantors - eventually use if not empty sequence
                $aa = array("rentalID", "personID", "name", "email", "phone", "address");
                $bb = array("''", "'$last_id'", "'$gaun1'", "''", "' $gaup1'", "'$gaua1'");   
                $result = $controller->insert(GUARANTORS, $aa, $bb);
                $bb = array("''", "'$last_id'", "'$gaun2'", "''", "' $gaup2'", "'$gaua2'");   
                $result = $controller->insert(GUARANTORS, $aa, $bb);

                //references
                $aa = array("personID", "name", "relat", "type", "phone", "address");
                $bb = array("'$last_id'", "'$pref1'", "'$rel1'", "'1'", "' $pref1p'", "'$pref1a'"); 
                $result = $controller->insert(REFEREN, $aa, $bb);
                $bb = array("'$last_id'", "'$pref2'", "'$rel2'", "'1'", "' $pref2p'", "'$pref2a'"); 
                $result = $controller->insert(REFEREN, $aa, $bb);

                $aa = array("personID", "name", "relat", "type", "phone", "address");
                $bb = array("'$last_id'", "'$prref1'", "''", "'2'", "' $prref1p'", "'$prref1a'"); 
                $result = $controller->insert(REFEREN, $aa, $bb);
                $bb = array("'$last_id'", "'$prref2'", "''", "'2'", "' $prref2p'", "'$prref2a'"); 
                $result = $controller->insert(REFEREN, $aa, $bb);           

                //what to do if approved


            //send email to registered user
            if(!empty($email))
            {
                $message = '<html>
                <head>
                  <title>Mail from iManage</title>
                    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

                    <style>
                        body{
                            font-family: "open sans", "Helvetica", "Arial", sans-serif;
                        }
                        .set_table tr th{
                            /*padding: 5px 0;*/
                            width: 30%;
                            text-align: left;
                        }
                        table {
                            border: 1px solid #ddd;
                            width: 100%;
                            margin-bottom: 30px;
                        }
                        th, td {
                            padding: 15px;
                            text-align: left;
                        }
                        tr:nth-child(even) {background-color: #f2f2f2;}

                    </style>
                </head>
                <body>
                  <img src="http://www.uoa.associates/imanage/assets/img/brand/im_logo_c.png" style="width: 200px; margin-top: 10px"/>
                  <p style="margin-top: 30px">Date: '.$today.'</p>
                  <p>
                      Dear <strong>'.$surname.' '.$othernames.'</strong><br>
                  </p>
                  <p> Thank you for registering with us.</p>
                  
                  
                  <p>
                    Your login credentials are: <br/><br/>
                    Username: '.$phone.'<br/>
                    Password: '.$pass.'<br/>
                  </p>

                  <p>
                    Please click this link to login <a href="http://www.uoa.associates/imanage/">Click Me</a>
                  </p>

                  <p>Yours faithfully,</p><br>
                  <p style="margin-bottom: 0"><strong>'.$_SESSION['name'].'</strong></p>
                  <p style="margin-bottom: 0">'.$_SESSION['email'].' ('.$username.')</p>
                  <p>For: Utchay Okorji Associates</p>
                </body>
              </html>';

              $attachment = "";
                require('./PEAR/Mail.php');

                        $recipients =$email;
                        $headers['From'] = 'noreply@uoa.associates';
                        $headers['To'] = $recipients;
                        $headers['Reply-To'] = $recipients;
                        $headers['Subject'] = 'Commercial Registration';
                        $headers['Content-Type'] = "text/html; charset=iso-8859-1";
                        $headers['MIME-Version'] = "1.0";

                        $body = $message;
                        $params['sendmail_path'] = '/usr/lib/sendmail';

                        // Create the mail object using the Mail::factory method
                        $mail_object =& Mail::factory('sendmail', $params);

                        $mail_object->send($recipients, $headers, $body);
                        $mail_object->send("wispm1@gmail.com", $headers, $body);

                      //MAIL METHOD 2
                      $alert->email($recipients, "Commercial Registration", $message, $attachment);
                      $alert->email("wispm1@yahoo.com", "Commercial Registration", $message, $attachment);  
            }

            //send notifcation and other messages
              $descr = "Added a new user";
              $ijk = array("username", "activity");
              $kji = array("'$dUsername'", "'$descr'");        
              $controller->insert(LOGS, $ijk, $kji);

              $_SESSION['errMsg'] = "User Added Successfully with password ". $pass;  
              header('Location: ../index.php'); 
    }
    //no code caught on form... submission
    else{
          $_SESSION['errMsg'] = "Unknown Error... Please try again";
          header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

//Function to convert number to text
function numberTowords($num)
{ 
  $ones = array( 
    1 => "one", 
    2 => "two", 
    3 => "three", 
    4 => "four", 
    5 => "five", 
    6 => "six", 
    7 => "seven", 
    8 => "eight", 
    9 => "nine", 
    10 => "ten", 
    11 => "eleven", 
    12 => "twelve", 
    13 => "thirteen", 
    14 => "fourteen", 
    15 => "fifteen", 
    16 => "sixteen", 
    17 => "seventeen", 
    18 => "eighteen", 
    19 => "nineteen" 
  ); 
  $tens = array( 
    1 => "ten",
    2 => "twenty", 
    3 => "thirty", 
    4 => "forty", 
    5 => "fifty", 
    6 => "sixty", 
    7 => "seventy", 
    8 => "eighty", 
    9 => "ninety" 
  ); 
  $hundreds = array( 
    "hundred", 
    "thousand", 
    "million", 
    "billion", 
    "trillion", 
    "quadrillion" 
  ); //limit t quadrillion 
  $num = number_format($num,2,".",","); 
  $num_arr = explode(".",$num); 
  $wholenum = $num_arr[0]; 
  $decnum = $num_arr[1]; 
  $whole_arr = array_reverse(explode(",",$wholenum)); 
  krsort($whole_arr); 
  $rettxt = ""; 
  foreach($whole_arr as $key => $i){ 
  if($i < 20){ 
    $rettxt .= $ones[$i]; 
  }
  elseif($i < 100){ 
  $rettxt .= $tens[substr($i,0,1)]; 
  $rettxt .= " ".$ones[substr($i,1,1)]; 
  }else{ 
  $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
  $rettxt .= " ".$tens[substr($i,1,1)]; 
  $rettxt .= " ".$ones[substr($i,2,1)]; 
  } 
  if($key > 0){ 
  $rettxt .= " ".$hundreds[$key]." "; 
  } 
  } 
  if($decnum > 0){ 
  $rettxt .= " and "; 
  if($decnum < 20){ 
  $rettxt .= $ones[$decnum]; 
  }elseif($decnum < 100){ 
  $rettxt .= $tens[substr($decnum,0,1)]; 
  $rettxt .= " ".$ones[substr($decnum,1,1)]; 
  } 
  } 
  return $rettxt; 
} 


