<?php
	include 'dump_r.php';
	include 'ex_obj.php';
?><!DOCTYPE html>
<html>
	<head>
	</head>
	<body>
		<h1>dump_r()</h1>

		<h2>html (default)</h2>
		<?php dump_r($obj); ?>

		<h2>limited pre-expand and recursion</h2>
		<style>
			.dump_r .myVals > * {
				background: lightyellow !important;
			}
		</style>
		<?php dump_r($obj, 1, 2); ?>

		<h2>text-only</h2>
		<pre><?php
			$ascii = dump_r($obj, 1, 1000, true);
			echo htmlspecialchars($ascii, ENT_NOQUOTES);
		?></pre>

		<h2>limited recursion</h2>
		<pre><?php
			$ascii = dump_r($obj, 1, 1, true);
			echo htmlspecialchars($ascii, ENT_NOQUOTES);
		?></pre>
	</body>
</html>