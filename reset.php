<?php include_once("database.php") ?>
<html>

<head>
	<title>Group #5 CTF2</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<center>
		<h2>Reset Password</h2>
		<p class="requirements">Password Requirements: </p>
		<p class="requirements">At least one symbol; Have both upper and lower case letters; At least one number; Password length >= 8</p>
		<p id="error"></p>
		<form action="javascript:reset()" method="get">
			<table>
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
						<button id="btn1"> Reset Password </button>
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

		function reset() {
			const htmlParameters = window.location.search;
			const params = new URLSearchParams(htmlParameters);
			var token = params.get('token');
			console.log(token);
			// check if no token is passed in
			if (token == null) {
				alert("Please click on the reset link from your email!");
				window.location.href = "failure.php"
			}

			// get username from token
			jQuery.ajax({
				type: "GET",
				url: 'database.php',
				dataType: 'json',
				data: {
					functionname: 'getUsernameFromToken',
					arguments: [token]
				},
				success: function(obj, textstatus) {
					if (!('error' in obj)) {
						var username = obj.result;
						if (username == false) {
							alert("No username is related to this link!");
							window.location.href = "failure.php"
						} else {
							var password = document.getElementById("pass").value;
							var password2 = document.getElementById("pass2").value;

							var hashed_pass = md5(password);

							if (password.normalize() !== password2.normalize()) {
								document.getElementById("error").innerHTML = "The entered passwords do not match.";
							} else if (!isValidPassword(password)) {
								document.getElementById("error").innerHTML = "Password did not fit the password criteria.";
							} else {
								jQuery.ajax({
									type: "GET",
									url: 'database.php',
									dataType: 'json',
									data: {
										functionname: 'updatePassword',
										arguments: [token, hashed_pass]
									},
									success: function(obj, textstatus) {
										console.log(obj);
										if (!('error' in obj)) {
											var result = obj.result;
											console.log(result);
											if (result) {
												jQuery.ajax({
													type: "GET",
													url: 'database.php',
													dataType: 'json',
													data: {
														functionname: 'recordData',
														arguments: ["S"]
													},
													success: function(obj, textstatus) {
														window.location.href = "success.php";
													}
												});
											} else {
												window.location.href = "failure.php";
											}
										} else {
											console.log(obj.error);
										}
									},
									error: function(error) {
										console.log(error);
									}
								});
							}
						}
					} else {
						console.log(obj.error);
					}
				}
			});
		}
	</script>
</body>

</html>