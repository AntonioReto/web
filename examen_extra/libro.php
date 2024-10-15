<?php include("config.php"); ?>
<?php
$servername = "localhost";
$username = "root"; // Por defecto en XAMPP
$password = ""; // Por defecto en XAMPP no hay contraseña
$dbname = "libro_visitas";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<html>
<head>
    <title> Libro de visitas </title>
    <style>
        .mensaje {
            word-wrap: break-word; /* Asegura que las palabras largas se dividan */
            max-width: 400px; /* Establece un ancho máximo para los mensajes */
        }
    </style>
</head>
<body>
    <center><h2> Libro de visitas </h2></center>

    <script type="text/javascript">
        function AbrePopWin(url, name, settings){
            var ventana = window.open(url, name, settings);
            ventana.focus();
            return false;
        }
    </script>

    <table border="1" align="center" width="50%" cellspacing=0 cellpadding=0 style='border-collapse:collapse; border:none;'>
        <tr>
            <td align="center" style='border:solid windowtext 1px;'> <b> Firma el libro de Visitas </b> </td>
        </tr>
        <tr>
            <td style='border:solid windowtext 1px;'>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <table border="0" align="center" width="100%">
                        <tr>
                            <td> Tu nombre </td>
                            <td align="center"> <input type="text" name="nom" size="37"> </td>
                        </tr>
                        <tr>
                            <td> Correo </td>
                            <td align="center"> <input type="text" name="mail" size="37"> </td>
                        </tr>
                        <tr>
                            <td> Web </td>
                            <td align="center"> <input type="text" name="pagweb" size="37"> </td>
                        </tr>
                        <tr>
                            <td valign="top"> Comentario </td>
                            <td align="center"> <textarea name="comentario" cols="28" rows="4"></textarea></td>
                        </tr>
                    </table>
                    <table border="0" align="center" width="100%">
                        <tr>
                            <td align="center"><a href="" onclick="return AbrePopWin('iconos/help.htm','','menubar=no,scrollbars=no,toolbar=no,location=no,directories=no,status=no,resizable=no,width=220, height=190');">Iconos aquí</a></td>
                            <td align="center"><input type="reset" value="Limpiar"></td>
                            <td align="center"><input type="submit" value="Firmar Libro"></td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
    <br />

    <?php
        // Recogemos las variables por POST
        $Nombre = isset($_POST["nom"]) ? $_POST["nom"] : "";
        $Mail = isset($_POST["mail"]) ? $_POST["mail"] : "";
        $Web = isset($_POST["pagweb"]) ? $_POST["pagweb"] : "";
        $Comen = isset($_POST["comentario"]) ? $_POST["comentario"] : "";

        // Array de errores
        $error = array();

        // Comprobamos si se llenó algún valor
        if ($Nombre != "" || $Mail != "" || $Web != "" || $Comen != "") {
            // Borrar espacios en blanco
            $Nombre = trim($Nombre);
            $Mail = trim($Mail);
            $Web = trim($Web);
            $Comen = trim($Comen);

            // Quitamos código HTML
            $Nombre = strip_tags($Nombre);
            $Mail = strip_tags($Mail);
            $Web = strip_tags($Web);
            $Comen = strip_tags($Comen);

            // Validaciones
            if ($Nombre == "") array_push($error, "Debes llenar el campo nombre");
            if ($Comen == "") array_push($error, "Debes poner algún comentario");

            // Guardar los datos en la base de datos
            if (count($error) == 0) {
                // Prepara la declaración SQL
                $stmt = $conn->prepare("INSERT INTO mensajes (nombre, correo, web, mensaje) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $Nombre, $Mail, $Web, $Comen);
                
                // Ejecuta la declaración
                if ($stmt->execute()) {
                    echo "<p>Comentario guardado con éxito.</p>";
                } else {
                    array_push($error, "Error al guardar el comentario: " . $stmt->error);
                }

                // Cierra la declaración
                $stmt->close();
            }
        }

        // Mostrar los mensajes de error si hay
        if (count($error) != 0) {
    ?>
        <table border="1" align="center" width="50%" cellspacing=0 cellpadding=0 style='border-collapse:collapse; border:none;'>
            <tr>
                <td style='border:solid windowtext 1px;'>Errores: <br><br>
                <?php
                    for ($i = 0; $i < count($error); $i++)
                        echo $error[$i] . "<br>";
                ?>
                </td>
            </tr>
        </table>
        <br>
    <?php
        }

        // Mostrar los mensajes de la base de datos
        $result = $conn->query("SELECT * FROM mensajes ORDER BY fecha DESC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $Nombre = $row['nombre'];
                $Mail = $row['correo'];
                $Web = $row['web'];
                $Comen = $row['mensaje'];
                
                // Reemplazar iconos en el comentario
                $Comen = reemplazarIconos($Comen);
                // Separar cadenas largas
                $Comen = separarCadenasLargas($Comen);
                
                // Quitar el http:// si lo tiene
                if ($Web != "") $Web = "http://" . str_replace("http://", "", $Web);
    ?>
                <table border="1" align="center" width="50%" cellspacing=0 cellpadding=0 style='border-collapse:collapse; border:none;'>
                    <tr>
                        <td class="mensaje" style='border:solid windowtext 1px;'>
                            <?php if ($Web != "") echo '<a href="' . $Web . '">'; ?>
                            <?php if ($Mail != "") echo "<acronym title='Correo: " . $Mail . "'>"; ?>
                            <?php echo $Nombre; ?>
                            <?php if ($Mail != "") echo "</acronym>"; ?>
                            <?php if ($Web != "") echo '</a>'; ?>
                            Escribió: <br> <?php echo $Comen; ?>
                        </td>
                    </tr>
                </table>
                <br>
    <?php
            }
        } else {
            echo "<p>No hay comentarios disponibles.</p>";
        }

        $conn->close(); // Cierra la conexión
    ?>

    <!-- Función para reemplazar iconos -->
    <?php
    function reemplazarIconos($mensaje) {
        $iconos = array(   
            ":)" => '<acronym title=" :) "><img src="./iconos/Smilies - Ayuda_files/5841.gif" alt=":)" style="width:16px; height:16px;"></acronym>',             
            ":(" => '<acronym title=" :( "><img src="./iconos/Smilies - Ayuda_files/5840.gif" alt=":(" style="width:16px; height:16px;"></acronym>',
            ";)" => '<acronym title=" ;) "><img src="./iconos/Smilies - Ayuda_files/5941.gif" alt=";)" style="width:16px; height:16px;"></acronym>',
            ":D" => '<acronym title=" :D "><img src="./iconos/Smilies - Ayuda_files/5868.gif" alt=":D" style="width:16px; height:16px;"></acronym>',
            "8|" => '<acronym title=" 8| "><img src="./iconos/Smilies - Ayuda_files/56124.gif" alt="8|" style="width:16px; height:16px;"></acronym>',
            "-_-" => '<acronym title=" -_- "><img src="./iconos/Smilies - Ayuda_files/459545.gif" alt="-_-" style="width:16px; height:16px;"></acronym>',
            ":|" => '<acronym title=" :| "><img src="./iconos/Smilies - Ayuda_files/58124.gif" alt=":|" style="width:16px; height:16px;"></acronym>',
            "xD" => '<acronym title=" xD "><img src="./iconos/Smilies - Ayuda_files/12068.gif" alt="xD" style="width:16px; height:16px;"></acronym>',
            ":ig" => '<acronym title=" :ig "><img src="./iconos/Smilies - Ayuda_files/58105103.gif" alt=":ig" style="width:16px; height:16px;"></acronym>',
            "^_^" => '<acronym title=" ^_^ "><img src="./iconos/Smilies - Ayuda_files/949594.gif" alt="^_^" style="width:16px; height:16px;"></acronym>',
            ":cr" => '<acronym title=" :cr "><img src="./iconos/Smilies - Ayuda_files/5899114.gif" alt=":cr" style="width:16px; height:16px;"></acronym>',
            ":bl" => '<acronym title=" :bl "><img src="./iconos/Smilies - Ayuda_files/5898108.gif" alt=":bl" style="width:16px; height:16px;"></acronym>',
            ":co" => '<acronym title=" :co "><img src="./iconos/Smilies - Ayuda_files/5899111.gif" alt=":co" style="width:16px; height:16px;"></acronym>',
            ":fy" => '<acronym title=" :fy "><img src="./iconos/Smilies - Ayuda_files/58102121.gif" alt=":fy" style="width:16px; height:16px;"></acronym>',
            ":wa" => '<acronym title=" :wa "><img src="./iconos/Smilies - Ayuda_files/5811997.gif" alt=":wa" style="width:16px; height:16px;"></acronym>',
            ":ka" => '<acronym title=" :ka "><img src="./iconos/Smilies - Ayuda_files/5810797.gif" alt=":ka" style="width:16px; height:16px;"></acronym>',
            ":tu" => '<acronym title=" :tu "><img src="./iconos/Smilies - Ayuda_files/58116100.gif" alt=":tu" style="width:16px; height:16px;"></acronym>',
            ":td" => '<acronym title=" :td "><img src="./iconos/Smilies - Ayuda_files/58116117.gif" alt=":td" style="width:16px; height:16px;"></acronym>',
            ":p" => '<acronym title=" :p "><img src="./iconos/Smilies - Ayuda_files/58112.gif" alt=":p" style="width:16px; height:16px;"></acronym>',
            ":@" => '<acronym title=" :@ "><img src="./iconos/Smilies - Ayuda_files/5864.gif" alt=":@" style="width:16px; height:16px;"></acronym>',
            ":re" => '<acronym title=" :re "><img src="./iconos/Smilies - Ayuda_files/58114101.gif" alt=":re" style="width:16px; height:16px;"></acronym>',
        );
        
        // Reemplaza los iconos en el mensaje
        foreach ($iconos as $codigo => $html) {
            // Reemplaza cada código por su correspondiente imagen
            $mensaje = str_replace($codigo, $html, $mensaje);
        }
    
        return $mensaje;
    }

    function separarCadenasLargas($mensaje) {
        $maxLong = 60; // Establece el límite de longitud de la cadena
        $palabras = explode(" ", $mensaje);
        $mensajeSeparado = "";

        foreach ($palabras as $palabra) {
            if (strlen($palabra) > $maxLong) {
                $chunks = str_split($palabra, $maxLong);
                $mensajeSeparado .= implode(" ", $chunks) . " ";
            } else {
                $mensajeSeparado .= $palabra . " ";
            }
        }

        return trim($mensajeSeparado);
    }
    ?>
</body>
</html>
