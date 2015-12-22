<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params:
		u - the user.
		rg_order - the way to order records in the regions table.
		sys_order - the way to order the records in the systems table.
		db - the database being used. Use 'TravelMappingDev' for in-development systems. 
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
body, html {
  margin:0;
  border:0;
  padding:0;
  height:100%;
  max-height:100%;
  overflow: hidden;
  font-size:9pt;
  background-color:#EEEEFF;
}

#body {
position: fixed;
left: 0px;
top: 80px;
bottom: 0px;
width: 100%;
overflow:auto;
padding: 20px;
}

#body h2{
	margin: auto;
	text-align: center;
	padding: 10px;
}

table.nmptable {
font-size:8pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.nmptable  td, th {
border: solid black;
border-width: 1px;
}

table.nmptable2 td, th {
border-width: 0px;
}

table.nmptable tr td {
text-align:right;
}

table.pthtable {
font-size:10pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.pthtable  td, th {
border: solid black;
border-width: 1px;
}

table.pthtable tr td {
text-align:left;
}

table.gratable {
font-size:10pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
width: 50%;
background-color:white;
}

table.gratable  td, th {
border: solid black;
border-width: 1px;
}

table.gratable tr td {
text-align:left;
}

table.gratable tr:hover td {
	background-color: #CCCCCC;
}

table.tablesorter th.sortable:hover {
  background-color: #CCCCFF;
}

table tr.status-active td {
  background-color: #CCFFCC;
}
table tr.status-preview td {
  background-color: #FFFFCC;
}
table tr.status-devel td {
  background-color: #FFCCCC;
}
</style>
<!-- jQuery -->
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<!-- TableSorter -->
<script src="/lib/jquery.tablesorter.min.js"></script>
<title>
	<?php
		$user = "null";
		$rg_order = "region ascending";
		$sys_order = "countryCode DESC";

		if (array_key_exists("u",$_GET)) {
			$user = $_GET['u'];
		}

		if (array_key_exists("rg_order",$_GET)) {
			$rg_order = $_GET['rg_order'];
		}

		if (array_key_exists("sys_order",$_GET)) {
			$sys_order = $_GET['sys_order'];
		}

		echo "Traveler Stats for ".$user;

		$dbname = "TravelMapping";
		if (array_key_exists("db",$_GET)) {
		  $dbname = $_GET['db'];
		}

		// establish connection to db: mysql_ interface is deprecated, should learn new options
		$db = new mysqli("localhost","travmap","clinch",$dbname) or die("Failed to connect to database");

		# functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
		function startsWith($haystack, $needle) {
		    // search backwards starting from haystack length characters from the end
			return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
		}
		function endsWith($haystack, $needle) {
		    // search forward starting from end minus needle length characters
		    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
		}

		function colorScale($percent) {

		}
	?>
</title>
</head>
<body>
	<script type="text/javascript">
	$(document).ready(function()
    {
      $("#tierTable").tablesorter({
        sortList: [[0,0]],
        headers: {}
      });
      $("#regionsTable").tablesorter({
        sortList: [[0,0]],
        headers: {0:{sorter:false}, 5:{sorter:false}}
      });
      $("#systemsTable").tablesorter({
        sortList: [[0,0]],
        headers: {0:{sorter:false}, 9:{sorter:false}}
      });
    }
  	);
	</script>
	<div id="header">
  	<a href="/">Home</a>
  	<a href="/hbtest">Highway Browser</a>
	<form id="userselect">
		<label>User: </label>
		<input type="text" name="u" form="userselect" value="<?php echo $user ?>">
		<input type="submit">
	</form>
	<h1>Traveler Stats for <?php echo $user; ?>:</h1>
	</div>
	<div id="body">
		<div id="overall">
			<h2>Overall Stats</h2>
			<table class="tablesorter gratable" style="width: 30%" id="tierTable">
				<thead>
					<tr><th colspan="4">Clinched Mileage Overall</th></tr>
					<tr>
						<th class="sortable">Tier</th>
						<th class="sortable">Clinched Mileage</th>
						<th class="sortable">Overall Mileage</th>
						<th class="sortable">Percent Clinched</th>
					</tr>
				</thead>
				<tbody>
					<?php
						//First fetch overall mileage
						$sql_command = "SELECT sys.tier, "; 
    					$sql_command .= "ROUND(SUM(COALESCE(cr.mileage, 0)), 0) AS clinchedMileage, ";
    					$sql_command .= "ROUND(SUM(COALESCE(routes.mileage, 0)), 0) AS totalMileage, ";
    					$sql_command .= "ROUND(SUM(COALESCE(cr.mileage, 0)) / SUM(COALESCE(routes.mileage, 0)) * 100, 3) AS percentage ";
    					$sql_command .= "FROM routes "; 
						$sql_command .= "LEFT JOIN clinchedRoutes AS cr ";
    					$sql_command .= "ON routes.root = cr.route AND traveler = '".$user."' ";
    					$sql_command .= "INNER JOIN systems AS sys ";
    					$sql_command .= "ON routes.systemName = sys.systemName;";
    					echo "<!-- SQL:".$sql_command."-->";
    					$res = $db->query($sql_command);
    					$row = $res->fetch_assoc();
    					$res->free();   					
    					echo "<b><tr style=\"background-color:#EEEEFF\"><td>Overall</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$row['percentage']."%</td></tr></b>\n";

    					//Then fetch mileage by tier
						$sql_command = "SELECT sys.tier, "; 
    					$sql_command .= "ROUND(SUM(COALESCE(cr.mileage, 0)), 0) AS clinchedMileage, ";
    					$sql_command .= "ROUND(SUM(COALESCE(routes.mileage, 0)), 0) AS totalMileage, ";
    					$sql_command .= "ROUND(SUM(COALESCE(cr.mileage, 0)) / SUM(COALESCE(routes.mileage, 0)) * 100, 3) AS percentage ";
    					$sql_command .= "FROM routes "; 
						$sql_command .= "LEFT JOIN clinchedRoutes AS cr ";
    					$sql_command .= "ON routes.root = cr.route AND traveler = '".$user."' ";
    					$sql_command .= "INNER JOIN systems AS sys ";
    					$sql_command .= "ON routes.systemName = sys.systemName ";
    					$sql_command .= "GROUP BY sys.tier;";
 						echo "<!-- SQL:".$sql_command."-->";

 						$res = $db->query($sql_command);
						while ($row = $res->fetch_assoc()) {
							echo "<tr><td>Tier ".$row['tier']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$row['percentage']."%</td></tr>\n";
						}
						$res->free();
					?>
				</tbody>
				<tfoot><td colspan="4">*Mileage for routes on lower tiers may be missing or inaccurate.</td></tfoot>
			</table>
		</div>
		<h2>Stats by Region</h2>
		<table class="gratable tablesorter" id="regionsTable">
			<thead>
				<tr>
					<th colspan="5">Clinched Mileage by Region:</th>
				</tr>
				<tr>
					<th class="sortable">Region</th>
					<th class="sortable">Clinched Mileage</th>
					<th class="sortable">Overall Mileage</th>
					<th class="sortable">Percent Clinched</th>
					<th>Map</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql_command = "SELECT o.region, co.mileage as clinchedMileage, o.mileage as totalMileage FROM overallMileageByRegion AS o INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region WHERE co.traveler = '".$user."';";
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						$percent = round($row['clinchedMileage'] / $row['totalMileage'] * 100.0, 3);
				        echo "<tr onClick=\"window.document.location='user/region.php?u=".$user."&rg=".$row['region']."'\"><td>".$row['region']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$percent."%</td><td><a href=\"/hbtest/mapview.php?u=".$user."&rg=".$row['region']."\">Map</a></td></tr>";
				    }
			        $res->free();
				?>
				<tr><td colspan="5">*Regions with no mileage not shown</td>
			</tbody>
		</table>
		<h2>Stats by System</h2>
		<table class="gratable tablesorter" id="systemsTable">
			<thead>
				<tr>
					<th colspan="9">Clinched Mileage by System</th>
				</tr>
				<tr>
					<th class="sortable">Country</th>
					<th class="sortable">System Code</th>
					<th class="sortable">System Name</th>
					<th class="sortable">Tier</th>
					<th class="sortable">Status</th>
					<th class="sortable">Clinched Mileage</th>
					<th class="sortable">Total Mileage</th>
					<th class="sortable">Percent</th>
					<th>Map</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$sql_command = "SELECT sys.countryCode, sys.systemName, sys.level, sys.tier, sys.fullName, r.root, COALESCE(ROUND(SUM(cr.mileage), 2),0) AS clinchedMileage, COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage, COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 3), 0) AS percentage FROM systems as sys INNER JOIN routes AS r ON r.systemName = sys.systemName LEFT JOIN clinchedRoutes AS cr ON cr.route = r.root AND cr.traveler = '".$user."' GROUP BY r.systemName";
					$sql_command .= ";";
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						echo "<tr onClick=\"window.document.location='user/system.php?u=".$user."&sys=".$row['systemName']."'\" class=\"status-".$row['level']."\">";
						echo "<td>".$row['countryCode']."</td>";
						echo "<td>".$row['systemName']."</td>";
						echo "<td>".$row['fullName']."</td>";
						echo "<td>Tier ".$row['tier']."</td>";
						echo "<td>".$row['level']."</td>";
						echo "<td>".$row['clinchedMileage']."</td>";
						echo "<td>".$row['totalMileage']."</td>";
						echo "<td>".$row['percentage']."%</td>";
						echo "<td><a href=\"/hbtest/mapview.php?u=".$user."&sys=".$row['systemName']."\">Map</a></td></tr>";
					}
					$res->free();
				?>
			</tbody>
		</table>
	</div>
</body>