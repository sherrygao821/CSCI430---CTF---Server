<?php include_once("database.php") ?>

<html>

<head>
	<title>Group #5 CTF2</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<center>
		<h2>Login</h2>
		<form action="javascript:login()" method="get">
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
					<td></td>
					<td>
						<button id="btn1"> Login </button>
					</td>
				</tr>
				<tr></tr>
				<tr>
					<td></td>
					<td>
						<a href="register.php">Register Here</a>
					</td>
				</tr>
			</table>
		</form>
	</center>

	<script src="jquery-3.6.0.js"></script>
	<script src="md5.min.js"></script>
	<script>
		function login() {
			var username = document.getElementById("user").value;
			var password = document.getElementById("pass").value;

			jQuery.ajax({
				type: "GET",
				url: 'database.php',
				dataType: 'json',
				data: {
					functionname: 'checkUsername',
					arguments: [username, "login"]
				},

				success: function(obj, textstatus) {
					if (!('error' in obj)) {
						console.log(obj);
						var isRegistered = obj.result;
						// if it is registered
						if (isRegistered) {
							jQuery.ajax({
								type: "GET",
								url: 'database.php',
								dataType: 'json',
								data: {
									functionname: 'getPassword',
									arguments: [username, password]
								},

								success: function(obj, textstatus) {
									var hashed_pass = md5(password);
									if (!('error' in obj)) {
										var storedPass = obj.result;
										// TODO: switch to hashed password
										if (storedPass == hashed_pass) {
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
											window.location.href = "failure.php?user=" + username;
										}
									} else {
										console.log(obj.error);
									}
								}
							});
						} else {
							window.location.href = "failure.php?user=" + username;
						}
					} else {
						console.log(obj.error);
					}
				},
				async: false
			});
		}
	</script>
</body>

</html>