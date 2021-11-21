<?php include_once("database.php") ?>

<html>

<head>
	<title>Group #5 CTF2</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<center>
		<h2>Register</h2>
		<p class="requirements">Password Requirements: </p>
		<p class="requirements">At least one symbol; Have both upper and lower case letters; At least one number; Password length >= 8</p>
		<p id="error"></p>
		<form action="javascript:register()" method="get">
			<table>
				<tr>
					<td class="txt">Username: </td>
					<td>
						<input type="text" name="user" id="user" required>
					</td>
				</tr>
				<tr>
					<td class="txt">Password: </td>
					<td>
						<input type="password" name="pass" id="pass" required>
					</td>
				</tr>
				<tr>
					<td class="txt">Verify Password: </td>
					<td>
						<input type="password" name="pass2" id="pass2" required>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<button id="btn1"> Register </button>
					</td>
				</tr>
				<tr></tr>
				<tr>
					<td></td>
					<td>
						<a href="login.php">Login Here</a>
					</td>
				</tr>
			</table>
		</form>
	</center>

	<script src="jquery-3.6.0.js"></script>
	<script src="md5.min.js"></script>
	<script>
		function isValidPassword(password) {
			if (password.match(/^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/)) {
				return true;
			}
			return false;
		}

		function register() {
			var username = document.getElementById("user").value;
			var password = document.getElementById("pass").value;
			var password2 = document.getElementById("pass2").value;

			// check whether two passwords equal to each other
			if (password.normalize() !== password2.normalize()) {
				document.getElementById("error").innerHTML = "The entered passwords do not match.";
			}
			// check whether the password fits into password policy
			else if (!isValidPassword(password)) {
				document.getElementById("error").innerHTML = "Password did not fit the password criteria.";
			}
			// check if username exists in database
			else if (true) {
				var hashed_pass = md5(password);
				// console.log(hashed_pass);
				jQuery.ajax({
					type: "GET",
					url: 'database.php',
					dataType: 'json',
					data: {
						functionname: 'checkUsername',
						arguments: [username, "register"]
					},
					success: function(obj, textstatus) {
						if (!('error' in obj)) {
							var isRegistered = obj.result;
							// if it is registered
							if (isRegistered) {
								// return true;
								document.getElementById("error").innerHTML = "The username exists already. Please enter a different username.";
							}
							// else return false;
							else {
								var user_pass = username + " " + password;
								jQuery.ajax({
									type: "GET",
									url: 'database.php',
									dataType: 'json',
									// TODO: change into hashed password
									data: {
										functionname: 'setPassword',
										arguments: [username, hashed_pass, user_pass]
									},

									success: function(obj, textstatus) {
										if (!('error' in obj)) {
											var isPasswordSet = obj.result;
											// if it is registered
											if (isPasswordSet) {
												jQuery.ajax({
													type: "GET",
													url: 'database.php',
													dataType: 'json',
													data: {
														functionname: 'recordData',
														arguments: ["S"]
													},
													success: function(obj, textstatus) {
														console.log("hi");
														window.location.href = "success.php";
													}
												});
											}
										} else {
											console.log(obj.error);
										}
									}
								});
							}
						} else {
							console.log(obj.error);
						}
					},
					async: false
				});
			}
		}
	</script>
</body>

</html>