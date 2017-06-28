<?php
/**
 * Template Name: Women Creating Wealth
 *
 * Template to display the twelve most recent posts, excepting the four found on home page, in box form. No comments.
 *
 * @package lisainspires
 *<h1 style="color: #F05E22; font-size: 20px; font-size: 2.0rem; font-weight: 500; line-height: 1.1;">Order Your Copy Today</h1>
 */
get_header();

session_start();

class DBController
{
    private $host = "localhost";
    private $user = "c0blog51_wp997";
    private $password = "2SPS7-E2)6";
    private $database = "c0blog51_wp997";
    private $conn;

    function __construct()
    {
        $this->conn = $this->connectDB();
    }

    function connectDB()
    {
        $conn = mysqli_connect($this->host, $this->user, $this->password, $this->database);
        return $conn;
    }

    function runQuery($query)
    {
        $result = mysqli_query($this->conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        if (!empty($resultset))
            return $resultset;
    }

    function numRows($query)
    {
        $result = mysqli_query($this->conn, $query);
        $rowcount = mysqli_num_rows($result);
        return $rowcount;
    }
}


$db_handle = new DBController();
if (!empty($_GET["action"])) {
    switch ($_GET["action"]) {
        case "add":
            if (!empty($_POST["quantity"]) && !empty($_POST["price"]) && $_POST["quantity"] != 0) {
                $productByCode = $db_handle->runQuery("SELECT * FROM products WHERE product_code='" . $_GET["product_code"] . "'");
                $itemArray = array($productByCode[0]["product_code"] => array('product_name' => $productByCode[0]["product_name"], 'product_code' => $productByCode[0]["product_code"], 'quantity' => $_POST["quantity"], 'price' => $_POST["price"]));

                if (!empty($_SESSION["cart_item"])) {
                    if (in_array($productByCode[0]["product_code"], array_keys($_SESSION["cart_item"]))) {
                        foreach ($_SESSION["cart_item"] as $k => $v) {
                            if ($productByCode[0]["product_code"] == $k) {
                                if (empty($_SESSION["cart_item"][$k]["quantity"])) {
                                    $_SESSION["cart_item"][$k]["quantity"] = 0;
                                }
                                $_SESSION["cart_item"][$k]["quantity"] += $_POST["quantity"];
                            }
                        }
                    } else {
                        $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"], $itemArray);
                    }
                } else {
                    $_SESSION["cart_item"] = $itemArray;
                }
            } else {
                if (empty($_POST["quantity"]) || $_POST["quantity"] == 0) {
                    echo '<h1 class="widget-title" style="margin-bottom:10px;">Number of books cannot be 0 or empty!!</h1>';
                } else if (empty($_POST["price"])) {
                    echo '<h1 class="widget-title" style="margin-bottom:10px;">Please select the your country to set the price per book</h1>';
                }
            }
            break;
        case "remove":
            if (!empty($_SESSION["cart_item"])) {
                foreach ($_SESSION["cart_item"] as $k => $v) {
                    if ($_GET["product_code"] == $k)
                        unset($_SESSION["cart_item"][$k]);
                    if (empty($_SESSION["cart_item"]))
                        unset($_SESSION["cart_item"]);
                }
            }
            break;
        case "empty":
            unset($_SESSION["cart_item"]);
            break;
    }
}
?>
    <style>
        #shopping-cart table {
            width: 30%;
            background-color: #F0F0F0;
        }

        #shopping-cart table td {
            background-color: #FFFFFF;
        }

        .txt-heading {
            padding: 10px 10px;
            border-radius: 2px;
            color: #73943D;;
            background: #FFF;
            margin-bottom: 10px;
        }

        a.btnRemoveAction {
            color: #F05E22;
            border: 0;
            padding: 2px 10px;
            font-size: 0.9em;
        }

        a.btnRemoveAction:visited {
            color: #482511;
            border: 0;
            padding: 2px 10px;
            font-size: 0.9em;
        }

        #btnEmpty {
            background-color: #ffffff;
            border: #73943D 1px solid;
            padding: 1px 10px;
            color: #ff0000;
            font-size: 0.8em;
            float: right;
            text-decoration: none;
            border-radius: 4px;
        }

        .btnAddAction {
            background-color: #F05E22;
            border: 0;
            padding: 3px 10px;
            color: #ffffff;
            margin-left: 2px;
            border-radius: 2px;
        }

        #shopping-cart {
            margin-bottom: 30px;
        }

        .cart-item {
            border-bottom: #79b946 1px dotted;
            padding: 10px;
        }

        #product-grid {
            margin-bottom: 30px;
        }

        .product-item {
            float: left;
            height: 300px;
            background: #ffffff;
            margin: 15px 10px;
            padding: 5px;
            border: #CCC 1px solid;
            border-radius: 4px;
        }

        .product-item div {
            text-align: center;
            margin: 10px;
        }

        .product-price {
            color: #005dbb;
            font-weight: 600;
        }

        .product-image {
            height: 100px;
            background-color: #FFF;
        }

        .clear-float {
            clear: both;
        }

        .demo-input-box {
            border-radius: 2px;
            border: #CCC 1px solid;
            padding: 2px 1px;
        }

        input.product-price {
            pointer-events: none;
            color: #AAA;
            background: #F5F5F5;
        }
    </style>
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <div class="container">
                <div class="row">
                    <article class="col-md-4">

                        <?php
                        $product_array = $db_handle->runQuery("SELECT * FROM products ORDER BY id ASC");
                        if (!empty($product_array)) {
                            foreach ($product_array as $key => $value) {
                                ?>
                                <div class="product-item">
                                    <div class="product-image"><img width="200" height="320"
                                                                    src="<?php echo get_template_directory_uri(); ?>/<?php echo $product_array[$key]["product_img_name"]; ?>"
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                </div>
                </article>
                <article class="col-md-8">
                    <?php
                    $product_array = $db_handle->runQuery("SELECT * FROM products ORDER BY id ASC");
                    if (!empty($product_array)) {
                        foreach ($product_array as $key => $value) {
                            ?>
                            <form method="post"
                                  action=" ?action=add&product_code=<?php echo $product_array[$key]["product_code"]; ?>">
                                <div><h3 class="widget-title"
                                         style="margin-top:10px;"><?php echo $product_array[$key]["product_name"]; ?></h3>
                                </div>
                                <p><i style="color:#482511;"><?php echo $product_array[$key]["product_desc"]; ?></i></p>
                                <br>
                                <?php if (!empty($_POST["quantity"])) { ?>
                                    <div>Quantity: <input type="text" id="quantity" name="quantity"
                                                          value=<?php echo $_POST["quantity"] ?> size="2"/></div>
                                <?php } else {
                                    ?>
                                    <div>Quantity: <input type="text" id="quantity" name="quantity" value="1" size="2"/>
                                    </div>
                                <?php } ?>
                                <br/>

                                Your Country:
                                <?php if (!empty($_POST["countries"])) { ?>

                                    <select name="countries" id="countries" onchange="updateText('countries')">
                                        <option value="Selectkaka">Select</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo">Democratic Republic of Congo</option>
                                        <option value="Cota D'Ivoire">Cote d &#39; Ivoire</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libya">Libya</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra">Sierra Leone</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Tanzania">Tanzania</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="United States">United States</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </select>

                                <?php } else { ?>
                                    <select name="countries" id="countries" onchange="updateText('countries')">
                                        <option value="Select">Select</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo">Democratic Republic of Congo</option>
                                        <option value="Cota D'Ivoire">Cote d &#39; Ivoire</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libya">Libya</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra">Sierra Leone</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Tanzania">Tanzania</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="United States">United States</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </select>
                                <?php } ?>

                                <br/><br>
                                <?php if (!empty($_POST["price"])) { ?>
                                    <div>Price per book $: <input class="product-price" type="text" name="price"
                                                                  value=<?php echo $_POST["price"] ?> id="countriesText"
                                                                  size="2"/></div>
                                <?php } else { ?>
                                    <div>Price per book $: <input class="product-price" type="text" name="price"
                                                                  value="" id="countriesText" size="2"/></div>
                                <?php } ?>

                                <br/>
                                <div>
                                    <input style="background-color: #F05E22; border: 0; color: #ffffff; margin-left: 50px; border-radius: 2px;"
                                           type="submit" value="Add to cart" class="btnAddAction"/></div>
                                <br>
                                <p><strong>*Please ensure you have the correct delivery address on PayPal for the book
                                        before finalising your order.</strong></p>

                            </form>
                            <?php
                        }
                    }
                    ?>
                </article>
            </div><!-- .container -->

        </main> <!-- #main -->
        <h3>If you wish to do a bank transfer, send an email to <a>info@sheinspiresher.com</a> with your
            shipping address and the number of books and send USD payment to:</3>
        <p>Lisa O&apos;Donoghue-Lindy <br> <strong>Account number:</strong> 6485809<br><strong>SWIFT:</strong>
            SFRUUS33<br><strong>ABA number:</strong> 2540-7417-0<br>Bank Fund Staff First Credit Union, <br>1725
            I street, Washington DC USA</p>
    </div><!-- #primary -->
    <div id="shopping-cart">
        <div class="txt-heading"><strong>Shopping Cart </strong></div>
        <?php
        if (isset($_SESSION["cart_item"])) {
            $item_total = 0;
            ?>
            <table cellpadding="10" cellspacing="1">
                <tbody>
                <tr>
                    <th style="text-align:left;"><strong>Name</strong></th>
                    <th style="text-align:center;"><strong>Qty</strong></th>
                    <th style="text-align:right;"><strong>Price</strong></th>
                    <th style="text-align:center;"><strong>Action</strong></th>
                </tr>
                <?php
                foreach ($_SESSION["cart_item"] as $item) {
                    ?>
                    <tr>
                        <td style="text-align:left;border-bottom:#F0F0F0 1px solid;">
                            <strong><?php echo $item["product_name"]; ?></strong></td>
                        <td style="text-align:center;border-bottom:#F0F0F0 1px solid;"><?php echo $item["quantity"]; ?></td>
                        <td style="text-align:right;border-bottom:#F0F0F0 1px solid;"><?php echo "$" . $item["price"]; ?></td>
                        <td style="text-align:center;border-bottom:#F0F0F0 1px solid;"><a
                                    href=" ?action=remove&product_code=<?php echo $item["product_code"]; ?>"
                                    class="btnRemoveAction" style="color: #F05E22;">Remove Item</a></td>
                    </tr>

                    <?php
                    $item_total += ($item["price"] * $item["quantity"]);
                }
                ?>

                <tr>
                    <td colspan="5" align=right><strong>Total:</strong> <?php echo "$" . $item_total; ?></td>
                </tr>
                <tr>
                    <td colspan="5" align=right><strong><br><a href="paypal-express-checkout"><img
                                        src="<?php echo get_template_directory_uri(); ?>/images/btn_pay_with_paypal.png"
                                        width="179" height="36" alt="PayPal - The safer, easier way to pay online!"><br><img
                                        alt=""
                                        src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg"
                                        width="100" height="20"></a></td>
                </tr>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>

    <script type="text/javascript">

        function SelectElement(valueToSelect) {
            var element = document.getElementById('countries');
            element.value = valueToSelect;
        }

        function updateText(type) {
            var id = type + 'Text';
            if (document.getElementById(type).value == 'United States') {
                if (document.getElementById('quantity').value <= 4) {
                    document.getElementById(id).value = 30;
                }
                else if (document.getElementById('quantity').value <= 9 && document.getElementById('quantity').value >= 5) {
                    document.getElementById(id).value = 27;
                }
                else if (document.getElementById('quantity').value <= 24 && document.getElementById('quantity').value >= 10) {
                    document.getElementById(id).value = 25;
                }
                else if (document.getElementById('quantity').value <= 49 && document.getElementById('quantity').value >= 25) {
                    document.getElementById(id).value = 24;
                }
                else if (document.getElementById('quantity').value <= 99 && document.getElementById('quantity').value >= 50) {
                    document.getElementById(id).value = 22;
                }
                else if (document.getElementById('quantity').value <= 499 && document.getElementById('quantity').value >= 100) {
                    document.getElementById(id).value = 20;
                }
                else if (document.getElementById('quantity').value >= 500) {
                    document.getElementById(id).value = 18;
                }

            }
            else {
                if (document.getElementById('quantity').value <= 4) {
                    document.getElementById(id).value = 32;
                }
                else if (document.getElementById('quantity').value <= 9 && document.getElementById('quantity').value >= 5) {
                    document.getElementById(id).value = 30;
                }
                else if (document.getElementById('quantity').value <= 24 && document.getElementById('quantity').value >= 10) {
                    document.getElementById(id).value = 28;
                }
                else if (document.getElementById('quantity').value <= 49 && document.getElementById('quantity').value >= 25) {
                    document.getElementById(id).value = 27;
                }
                else if (document.getElementById('quantity').value <= 99 && document.getElementById('quantity').value >= 50) {
                    document.getElementById(id).value = 25;
                }
                else if (document.getElementById('quantity').value <= 499 && document.getElementById('quantity').value >= 100) {
                    document.getElementById(id).value = 24;
                }
                else if (document.getElementById('quantity').value >= 500) {
                    document.getElementById(id).value = 20;
                }
            }

        }
    </script>
<?php get_footer(); ?>