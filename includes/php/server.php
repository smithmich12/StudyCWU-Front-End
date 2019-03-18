<?php
session_start();

// initializing variables
$username = "";
$email    = "";
$errors = array();

// connect to the database
$db = mysqli_connect('localhost', 'acjm446', '3BQqVQurcqsP', 'studycwu') or  die("could not connect");


// Cookie stuffs
if(isset($_COOKIE['rememberme'])){

  $parts = explode("|", $_COOKIE['rememberme']);
  $userid = $parts[0];
  $token = $parts[1];

  $query = "SELECT * FROM cwu_users WHERE id='$userid'";
  $results = mysqli_query($db, $query);
  $value = mysqli_fetch_assoc($results);  

  $dbToken = md5($value['id'].$value['username'].$value['password']);

  if($token == $dbToken){
    $_SESSION['userid'] = $value['id'];
  }

  
  
  //exit;
}



// REGISTER USER
if (isset($_POST['reg_user'])) {
  // receive all input values from the form
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);

  // form validation: ensure that the form is correctly filled ...
  // by adding (array_push()) corresponding error unto $errors array
  if (empty($username)) { array_push($errors, "Username is required"); }
  if (empty($email)) { array_push($errors, "Email is required"); }
  if (empty($password_1)) { array_push($errors, "Password is required"); }
  if ($password_1 != $password_2) {
	   array_push($errors, "The two passwords do not match");
  }
  if (substr($email, -8) != "@cwu.edu"){
    array_push($errors, "You must use a CWU email");
  }


  // first check the database to make sure 
  // a user does not already exist with the same username and/or email
  $user_check_query = "SELECT * FROM cwu_users WHERE username='$username' OR email='$email' LIMIT 1";
  $result = mysqli_query($db, $user_check_query);
  $user = mysqli_fetch_assoc($result);
  
  if ($user) { // if user exists
    if ($user['username'] === $username) {
      array_push($errors, "Username already exists");
    }

    if ($user['email'] === $email) {
      array_push($errors, "Email already exists");
    }
  }

  // Finally, register user if there are no errors in the form
  if (count($errors) == 0) {

   	$password = password_hash($password_1, PASSWORD_DEFAULT);//encrypt the password before saving in the database

  	$query = "INSERT INTO cwu_users (username, email, password) 
  			  VALUES('$username', '$email', '$password')";
  	mysqli_query($db, $query);


    $query = "SELECT id FROM cwu_users WHERE username='$username'";
    $results = mysqli_query($db, $query);
    $value = mysqli_fetch_assoc($results);


  	$_SESSION['userid'] = $value['id'];
  	$_SESSION['success'] = "You are now logged in";
  	header('Location: ../../index.php');
  }
}




// LOGIN USER
if (isset($_POST['login_user'])) {
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $password = mysqli_real_escape_string($db, $_POST['password']);
  if (empty($username)) {
    array_push($errors, "Username is required");
  }
  if (empty($password)) {
    array_push($errors, "Password is required");
  }

  if (count($errors) == 0) {
    


    $query = "SELECT * FROM cwu_users WHERE username='$username'";
    $results = mysqli_query($db, $query);
    $value = mysqli_fetch_assoc($results);

    if (password_verify($password, $value['password']) ) {
      if(isset($_POST['rememberme'])){

        $token = md5($value['id'].$value['username'].$value['password']);
        $cookie = $value['id']."|".$token;
        setcookie("rememberme", $cookie, time() + (86400 * 30));
      }

      $_SESSION['userid'] = $value['id'];
      $_SESSION['success'] = "You are now logged in";


      header('Location: '.$_SERVER['REQUEST_URI']);
    }else {
      array_push($errors, "Wrong username and/or password");
    }
  }
}

?>