<?php
session_start();
function run_sql_file($location, $prefix, $data_language) {
    $commands = '';
    $handle = @fopen($location, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            $line = $buffer;
            if (strpos($line, '{PREFIX}') !== false)
                $line = str_replace('{PREFIX}', $prefix . '_', $buffer);
            $commands .= $line . "\n";
        }
        if (!feof($handle)) {
            echo "Error: Unexpected failure of fgets()\n";
        }
        fclose($handle);
    }
    //convert to array
    $commandsArray = explode(";", $commands);
    //run commands
    $item = $total = $success = 0;
    $handle_log = null;
    foreach ($commandsArray as $command) {
        if (trim($command)) {
            if(mysql_query($command)){
                $success++;
            }else{
              if(is_null($handle_log)){
                 $handle_log = fopen('log/failed.log', "w");
                 if (!$handle) {
                    throw new Exception("Could not open the file log!");
                 }else{
                    fwrite($handle_log, "--------------------------------------------------------------------------------------------------------------------------------------------\n");
                    fwrite($handle_log, "-- Failed.log - Date Time: ".date('Y-m-d h:m:s',time())." - Installation: ".$data_language[0]->name."\n");
                    fwrite($handle_log, "--------------------------------------------------------------------------------------------------------------------------------------------\n");
                    fwrite($handle_log, "\n");
                 }
              }  
              fwrite($handle_log, "------------------------------[BEGIN COMMAND]------------------------------------------\n");
              fwrite($handle_log, "-- Nº Command: ".$item." - Command SQL failed: ".$command.";\n");
              fwrite($handle_log, "-------------------------------[END COMMAND]-------------------------------------------\n\n\n");
            }
            $item++;
            //$success += (@mysql_query($command) == false ? 0 : 1);
            $total += 1;
        }
    }
    if(!is_null($handle_log))fclose($handle_log);
    $failed = $total - $success;
    ini_set('auto_detect_line_endings', FALSE);
    //return number of successful queries and total number of queries found
    return array(
        "success" => $success,
        "total" => $total,
        "failed" => $failed
    );
}

$languages = array();
$xml = simplexml_load_file("setting.xml");
$n_languages = count($xml->languages->language);
for ($i = 0; $i <= $n_languages - 1; $i++) {
    $languages[] = $xml->languages->language[$i]['id'];
}
if ((isset($_POST['input_language'])) && (in_array($_POST['input_language'], $languages))) {
    $_SESSION['language'] = $_POST['input_language'];
}
if(!isset($_SESSION['language']))$_SESSION['language'] = $xml->languages->default;
$data_lang = include('languages'.DIRECTORY_SEPARATOR.$_SESSION['language'].'.php');
function translate($string){
    GLOBAL $data_lang;
    return $data_lang[$string];
}
$data_language = $xml->xpath('//language[@id="' . $_SESSION['language'] . '"]');

//check db connect
$error_connecting = true;
$run_sql = false;
$inputHost = '';
$inputUsername = '';
$inputPassword = '';
$inputDatabase = '';
$inputPrefix = '';
if (isset($_POST['inputHost'])) {
    error_reporting(0);
    $cn = mysql_connect($_POST['inputHost'], $_POST['inputUsername'], $_POST['inputPassword']);
    if ($cn) {
        $dbcheck = mysql_select_db($_POST['inputDatabase']);
        if ($dbcheck) {
            $inputHost = $_POST['inputHost'];
            $inputUsername = $_POST['inputUsername'];
            $inputPassword = $_POST['inputPassword'];
            $inputDatabase = $_POST['inputDatabase'];
            $inputPrefix = $_POST['inputPrefix'];
            $error_connecting = false;
        }
    }
} elseif (isset($_POST['InHost'])) {
    //error_reporting(0);
    $cn = mysql_connect($_POST['InHost'], $_POST['InUsername'], $_POST['InPassword']);
    if ($cn) {
        $dbcheck = mysql_select_db($_POST['InDatabase']);
        if ($dbcheck) {
            $result = run_sql_file('source/' . $xml->source, $_POST['InPrefix'], $data_language);
            $run_sql = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $xml->title ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="TeaFramework Installation Tool">
        <meta name="author" content="Basilio Fajardo Gálvez">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <style type="text/css">
			.navbar-static-top {
			  margin-bottom: 19px;
			}
        </style>
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
    </head>

   <!-- Static navbar -->
    <div class="navbar navbar-inverse navbar-static-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo $xml->title ?></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-globe"></span> <?php echo translate('Idioma') ?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
				<?php
				$n_choose = count($data_language[0]->choose->option);
				for ($z = 0; $z <= $n_choose - 1; $z++) {
					echo '<li><a href="#language" id="' . $data_language[0]->choose->option[$z]['value'] . '">' . $data_language[0]->choose->option[$z] . '</a></li>';
				}
				?>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
	
    <div class="container">
        <form action="" method="post" id="ch_language" name="ch_language">
            <input type="hidden" name="input_language" id="input_language" value="<?php echo $_SESSION['language'] ?>">
        </form>
        <form action="" method="post" id="InstallTables" name="InstallTables">
            <input type="hidden" name="InHost" id="InHost" value="<?php echo $inputHost ?>">
            <input type="hidden" name="InDatabase" id="InDatabase" value="<?php echo $inputDatabase ?>">
            <input type="hidden" name="InUsername" id="InUsername" value="<?php echo $inputUsername ?>">
            <input type="hidden" name="InPassword" id="InPassword" value="<?php echo $inputPassword ?>">
            <input type="hidden" name="InPrefix" id="InPrefix" value="<?php echo $inputPrefix ?>">
        </form>
        <div id="begin_install">
            <div class="jumbotron">
                <h1><?php echo $xml->title ?></h1>
                <p class="lead"><?php echo translate('Para finalizar el proceso de instalación de la aplicación se debe completar y seguir todos los pasos, hasta conseguir la instalación completa') ?></p>
                <a class="btn btn-lg btn-success" href="#step1"><?php echo translate('Empezar Ahora') ?></a>
            </div>
        </div>
        <div class="row" id="step1" style="display:none">
            <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">25%</small>&nbsp;
					<div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 25%;">
						<span class="sr-only">25% <?php echo translate('Completado') ?></span>
					  </div>
					</div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 1 - <?php echo translate('Requerimientos del sistema') ?></h3>                    
                </div>
                <?php
                $version = explode('.', PHP_VERSION);
                $ext = get_loaded_extensions();
                $extensions = array();
                foreach ($ext as $value) {
                    $extensions[] = strtolower($value);
                }
                $PHP_VERSION_ID = ($version[0] * 10000 + $version[1] * 100 + $version[2]);
                $version_setting = explode('.', $xml->requires->version);
                $n_point = count($version_setting);
                switch ($n_point) {
                    case 1: $required_version = ($version_setting[0] * 10000);
                        break;
                    case 2: $required_version = ($version_setting[0] * 10000 + $version_setting[1] * 100);
                        break;
                    case 3: $required_version = ($version_setting[0] * 10000 + $version_setting[1] * 100 + $version_setting[2]);
                        break;
                }
                define('INSTALL', '<img src="images/check.png" border="0" style="margin-right:5px" />');
                define('DISABLE', '<img src="images/disable.png" border="0" style="margin-right:5px" />');
                if ($PHP_VERSION_ID < $required_version)
                    echo '<div class="alert alert-danger"><img src="images/disable.png" border="0" style="margin-right:5px" />'.translate('Versión').' PHP: ' . PHP_VERSION . ' '.translate('La versión no es compatible').'</div>'; else
                    echo '<div class="alert alert-success"><img src="images/check.png" border="0" style="margin-right:5px" />'.translate('Versión').' PHP: ' . PHP_VERSION . '</div>';
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:80%;text-align:left"><?php echo translate('Extensión') ?></th>
                            <th style="width:20%;text-align:left"><?php echo translate('Estado') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $next = true;
                        foreach ($xml->requires->extension as $value) {
                            echo '<tr><td width="250">' . ucfirst($value['name']) . '</td><td width="150">';
                            if (in_array(strtolower($value['name']), $extensions))
                                echo INSTALL; else {
                                echo DISABLE;
                                $next = false;
                            }
                            echo '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table> 
                <ul class="pager">
                    <li class="disabled"><a href="#">&larr; <?php echo translate('Anterior') ?></a></li>
                    <li<?php if (!$next) echo ' class="disabled"'; ?>><a <?php if (!$next) echo 'href="#"'; else echo 'href="#next"'; ?>><?php echo translate('Siguiente') ?> &rarr;</a></li>
                </ul>
            </div>
        </div>
        <div class="row" id="step2" style="display:none">
            <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">50%</small>&nbsp;
					<div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 50%;">
						<span class="sr-only">50% <?php echo translate('Completado') ?></span>
					  </div>
					</div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 2 - <?php echo translate('Conexión con la Base de Datos') ?></h3>                    
                </div>
<?php if ((isset($_POST['inputHost'])) && ($error_connecting)) echo'<div class="alert alert-danger error_connecting"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Error!</strong> '.translate('No se ha podido establecer conexión con la base de datos').'.</div><script>$(function({$(\'.load_connect\').hide();})</script>'; elseif ((isset($_POST['inputHost'])) && (!$error_connecting)) echo'<div class="alert alert-success success_connecting"><button type="button" class="close" data-dismiss="alert">&times;</button>'.translate('La conexión con la base de datos se ha realizado con éxito').'.</div><script>$(function({$(\'.load\').hide();})</script>'; ?>
                <form action="" role="form" id="form_connect" name="form_connect" style="width:50%" method="post">
                    <div class="control-group">
                        <label for="inputHost"><?php echo translate('Nombre del Host') ?></label>
						<input type="text" class="form-control" id="inputHost" name="inputHost" value="<?php echo $xml->values->host ?>" required="required" />
						<span class="help-block"><?php echo translate('Nombre del Host ó dirección IP del servidor de la Base de Datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputDatabase"><?php echo translate('Base de Datos') ?></label>
						<input type="text" class="form-control" id="inputDatabase" name="inputDatabase" value="<?php echo $xml->values->database ?>" required="required" />
						<span class="help-block"><?php echo translate('Nombre de la Base de datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputUsername"><?php echo translate('Nombre de Usuario') ?></label>
						<input type="text" class="form-control" id="inputUsername" name="inputUsername" value="<?php echo $xml->values->username ?>" required="required" />
						<span class="help-block"><?php echo translate('Nombre de usuario para la conexión con la Base de Datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputPassword"><?php echo translate('Contraseña') ?></label>
						<input type="password" class="form-control" id="inputPassword" name="inputPassword" value="" required="required" />
						<span class="help-block"><?php echo translate('Contraseña de la Base de Datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputPrefix"><?php echo translate('Prefijo') ?></label>
						<input type="text" class="form-control" id="inputPrefix" name="inputPrefix" value="<?php echo $xml->values->prefix ?>" required="required" />
						<span class="help-block"><?php echo translate('Prefijo de las tablas') ?></span>
                    </div>
                </form>                
            </div>    
            <ul class="pager">
                <li><a href="#back">&larr; <?php echo translate('Anterior') ?></a></li>
                <li><span class="load" style="display:none"><img src="images/load.gif" style="margin-right:5px;vertical-align:middle" alt="Installing..." /><?php echo translate('Espere, creando estructura de tablas') ?>...</span><span class="load_connect" style="display:none"><img src="images/load.gif" style="margin-right:5px;vertical-align:middle" alt="Connecting..." /><?php echo translate('Espere, conectando con la Base de Datos') ?>...</span> <a <?php if ($error_connecting) echo 'href="#connect_db"'; else echo 'href="#datatables"'; ?>><?php echo translate('Siguiente') ?> &rarr;</a></li>
            </ul>
        </div>
        <div class="row" id="step3" style="display:none">
            <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">100%</small>&nbsp;
					<div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
						<span class="sr-only">100% <?php echo translate('Completado') ?></span>
					  </div>
					</div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 3 - <?php echo translate('Instalación Completada') ?></h3>                    
                </div>
                <div class="alert alert-warning" style="font-size:16px;margin-bottom:45px">
                    <span class="glyphicon glyphicon-warning-sign"></span> <?php echo translate('La instalación ha sido completada. Para mayor seguridad borre la carpeta') ?> <b>installation</b>.
                </div>
                <div class="well" style="padding-bottom:45px;margin-bottom:50px">
                    <h3 style="margin-left:25px"><?php echo translate('Resumen de la Instalación') ?></h3>
                    <div>
                    <table border="0" cellspacing="10" style="margin-left:25px">
                        <tr>
                            <td style="width:300px" class="text-success"><?php echo translate('Sentencias SQL Ejecutadas con éxito') ?>:</td><td class="text-success"><b><?php if(isset($result['success'])) echo $result['success']; ?></b></td>
                        </tr>
						<?php echo($result['failed'] >= 1)?'<tr><td><code>'.translate('Las sentencias fallidas están localizadas en el archivo').': log/failed.log</code></td></tr>':''; ?>
                        <tr>
                            <td class="text-error"><?php echo translate('Sentencias SQL Fallidas') ?>:</td><td class="text-error"><b><?php if(isset($result['failed'])) echo $result['failed']; ?></b></td>
                        </tr>
                        <tr>
                            <td class="text-info"><?php echo translate('Total Sentencias SQL Ejecutadas') ?>:</td><td class="text-info"><b><?php if(isset($result['total'])) echo $result['total']; ?></b></td>
                        </tr>
                    </table>
                    </div>
                </div>                
            </div>    
        </div>
        <hr/>
        <div class="footer">
            <p>&copy; <?php echo $xml->copyright ?></p>
        </div>
    </div> <!-- /container -->

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/install.js"></script>
    <script>
        $(document).ready(function(){
<?php
if (!$run_sql) {
    if (isset($_POST['inputHost']))
        echo '$("#begin_install").hide(); $("#step2").show(); $(".step").html("2/3"); step=2; $("#inputHost").val("' . $_POST['inputHost'] . '"); $("#inputDatabase").val("' . $_POST['inputDatabase'] . '"); $("#inputUsername").val("' . $_POST['inputUsername'] . '"); $("#inputPassword").val("' . $_POST['inputPassword'] . '"); $("#inputPrefix").val("' . $_POST['inputPrefix'] . '");';
    if ((isset($_POST['inputHost'])) && (!$error_connecting))
        echo '$(".form-control").attr("disabled","disabled")';
}else {
    echo'$("#begin_install").hide(); $("#step3").show(); $(".step").html("3/3");';
}
?>
    });
    </script>
</body>
</html>
