<?php
	require __DIR__ . '/../dump_r.php';
	include __DIR__ . '/obj.php';

//	dump_r\Rend::$sql_pretty = true;
//	dump_r\Rend::$xml_pretty = true;
//	dump_r\Rend::$json_pretty = true;

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
		<?php
			dump_r\Type::$dic = array();
			dump_r($obj, false, true, 2, 1);
		?>

		<h2>text-only</h2>
		<pre><?php
			dump_r\Type::$dic = array();
			$ascii = dump_r($obj, true, false, 1e3, 1);
			echo htmlspecialchars($ascii, ENT_NOQUOTES);
		?></pre>

		<h2>limited recursion</h2>
		<pre><?php
			dump_r\Type::$dic = array();
			$ascii = dump_r($obj, true, false, 1, 1);
			echo htmlspecialchars($ascii, ENT_NOQUOTES);
		?></pre>

		<a href="https://github.com/leeoniya/dump_r.php"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>
	</body>
</html>