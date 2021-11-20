<!DOCTYPE html>
<html>
	<head>
		<title>Group #5 CTF2</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<center>
			<h2 id="fmsg">Failed to Login.</h2>
			<h3 id="fmsg">The entered Username and Password combination was incorrect.</h3>
			<table>
					<tr>
						<td></td>
						<td>
							<a href="/">Try Logging In Again</a>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<button onclick="sendEmail()">Send Reset Email to the Entered Username Email.</button>
						</td>
					</tr>
				</table>
		</center>

		<script src="jquery-3.6.0.js"></script>
		<script>
			function sendEmail() {
				const htmlParameters = window.location.search;
				const params = new URLSearchParams(htmlParameters);
				var username = params.get('user');
				jQuery.ajax({
				type: "GET",
				url: 'database.php',
				dataType: 'json',
				data: {
					functionname: 'sendEmail',
					arguments: [username]
				},

				success: function(obj, textstatus) {
					if (!('error' in obj)) {
						console.log(obj);
						var status = obj.result;
						if (status) {
							alert("Successfully sent reset email. Please check your email!");
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