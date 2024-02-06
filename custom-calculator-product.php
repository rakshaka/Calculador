<?php
/*
Plugin Name: Calculadora Personalizada
Description: Plugin para calcular precios según dimensiones en una tabla personalizada.
Version: 1.0
Author: Tu Nombre
*/

// Función para mostrar el formulario de la calculadora
function display_custom_calculator_form() {
    ob_start();
    ?>
    <form method="post" id="custom_calculator_form">
        <label for="ancho">Ancho (mm):</label>
        <input type="number" name="ancho" id="ancho" min="1" max="1800" required>
        <br>
        <label for="alto">Alto (mm):</label>
        <input type="number" name="alto" id="alto" min="1" max="3500" required>
        <br>
        <button type="button" id="calculate_price_button">Calcular Precio</button>
    </form>
    <div id="custom_calculator_result"></div>
    <script>
        // Manejar el clic en el botón "Calcular Precio"
        document.getElementById('calculate_price_button').addEventListener('click', function() {
            var ancho = document.getElementById('ancho').value;
            var alto = document.getElementById('alto').value;
            var data = {
                'action': 'calculate_price',
                'ancho': ancho,
                'alto': alto
            };
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                document.getElementById('custom_calculator_result').innerHTML = response;
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Función para manejar la solicitud AJAX y calcular los precios
function calculate_custom_price() {
    if (isset($_POST['ancho']) && isset($_POST['alto'])) {
        $ancho = intval($_POST['ancho']);
        $alto = intval($_POST['alto']);

        // Ajustar el ancho al siguiente múltiplo de 100
        $ancho = ceil(max(500, $ancho) / 100) * 100;
        // Ajustar el alto al siguiente múltiplo de 100
        $alto = ceil(max(500, $alto) / 100) * 100;

        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT * FROM wp_precios_base WHERE ref <= %d ORDER BY ref DESC LIMIT 1",
            $alto
        );
        $result = $wpdb->get_row($query);

        if ($result) {
            $precio_base = $result->{$ancho};
            if ($precio_base) {
                // Precio común
                $precio_comun = number_format($precio_base, 2);
                // Precio Negro
                $precio_negro = number_format($precio_base * 0.65 * 3, 2);
                // Precio Premium
                $precio_premium = number_format($precio_base * 0.65 * 3.5, 2);

                // Mostrar las opciones de precio y el campo de cantidad
                echo "<div>
                        <label>Seleccione el precio:</label>
                        <br>
                        <input type='radio' id='precio_base' name='precio' value='$precio_base'>
                        <label for='precio_base'>Precio Base: $precio_base</label>
                        <br>
                        <input type='radio' id='precio_comun' name='precio' value='$precio_comun'>
                        <label for='precio_comun'>Precio Común: $precio_comun</label>
                        <br>
                        <input type='radio' id='precio_negro' name='precio' value='$precio_negro'>
                        <label for='precio_negro'>Precio Negro: $precio_negro</label>
                        <br>
                        <input type='radio' id='precio_premium' name='precio' value='$precio_premium'>
                        <label for='precio_premium'>Precio Premium: $precio_premium</label>
                    </div>";
                echo "<div><label for='cantidad'>Cantidad:</label>
                        <input type='number' name='cantidad' id='cantidad' min='1' value='1' required></div>";
                echo "<button type='button' id='add_to_cart_button'>Agregar al Carrito</button>";
            } else {
                echo "No se encontró un precio para el ancho proporcionado.";
            }
        } else {
            echo "No se encontró un precio para las dimensiones proporcionadas.";
        }
    } else {
        echo "Por favor, ingrese valores válidos para el ancho y el alto.";
    }
    wp_die();
}

// Agregar el shortcode y el manejador de la solicitud AJAX
add_shortcode('custom_calculator', 'display_custom_calculator_form');
add_action('wp_ajax_calculate_price', 'calculate_custom_price');
add_action('wp_ajax_nopriv_calculate_price', 'calculate_custom_price');
?>
