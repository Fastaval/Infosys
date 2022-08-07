<?php
mysql_connect('db','fastaval2016','za1oa,Roh4aephee') or die("No DB");
mysql_select_db("infosys2016") or die("No select DB");
$search = $_GET['search'];
header("Content-type: text/html; charset=iso-8859-1");
?>
<!DOCTYPE html>
<html>
<head>
<title>Afsendte SMS'er</title>
</head>
<body>
<h1>Afsendte SMS'er</h1>
<form action="allsms.php">
<p>
S&oslash;g i beskeder: <input type="text" name="search" value="<?php print htmlspecialchars($search); ?>">
</p>
</form>
<?php
$query = "SELECT deltager_id, sendt, besked FROM smslog ORDER BY id DESC LIMIT 100";
if ($search) {
	$query = "SELECT deltager_id, sendt, besked FROM smslog WHERE besked LIKE '%" . mysql_real_escape_string($search) . "%' ORDER BY id DESC";
}
$result = mysql_query($query);
print "<table><tr><th>Deltager ID</th><th>Besked</th><th>Tidspunkt</th></tr>";
while($row = mysql_fetch_array($result) ) {
	print "<tr>";
	print "<td style=\"text-align: right;\"><a href=\"/deltager/visdeltager/" . $row['deltager_id'] . "\">" . $row['deltager_id'] . "</a></td>";
	print "<td>" . $row['besked'] . "</td>";
	print "<td>" . $row['sendt'] . "</td>";
	print "</tr>\n";
}
print "</table>";

?>
</body>
</html>
