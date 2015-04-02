<div id="login">Logged is as: <?php echo $_SESSION['login_user']; ?><br/><a href="logout.php">Log out</a></div>
<div id="header">
    
    <a href="index.php" title="Homepage"><img id="logo" src="images/katek_white.png" alt="Katek Hungary Kft."/></a>

    <nav>
        <ul>
            <li>
                <a href="#">View Current Status</a>
                <ul class="fallback">
                    <li><a href="ajaxproba.php?id=1">LINE 1</a></li>
                    <li><a href="ajaxproba.php?id=2">LINE 2</a></li>
                    <li><a href="ajaxproba.php?id=3">LINE 3</a></li>
                    <li><a href="ajaxproba.php?id=4">LINE 4</a></li>
                </ul>
            </li>
            <!-- <li>
                <a href="#">Production Setup</a>
                <ul class="fallback">
                    <li><a href="productSelect.php?id=1">LINE 1</a>
                        <ul class="second-level">
                            <li><a href="productSelect.php?id=1">SMT</a></li>
                            <li><a href="productSelect.php?id=1">THT</a></li>
                            <li><a href="productSelect.php?id=1">ICT</a></li>
                            <li><a href="productSelect.php?id=1">FCT</a></li>
                            <li><a href="productSelect.php?id=1">ASSEMBLING</a></li>
                        </ul>
                    </li>
                    <li><a href="productSelect.php?id=2">LINE 2</a>
                        <ul class="second-level">
                            <li><a href="productSelect.php?id=2">SMT</a></li>
                            <li><a href="productSelect.php?id=2">THT</a></li>
                            <li><a href="productSelect.php?id=2">ICT</a></li>
                            <li><a href="productSelect.php?id=2">FCT</a></li>
                            <li><a href="productSelect.php?id=2">ASSEMBLING</a></li>
                        </ul>
                    </li>
                    <li><a href="productSelect.php?id=3">LINE 3</a></li>
                    <li><a href="productSelect.php?id=4">LINE 4</a></li>
                </ul>
            </li>-->
            <li>
                <a href="#">Production Setup</a>
                <ul class="fallback">
                    <li><a href="changeProducts.php?id=1">LINE 1</a></li>
                    <li><a href="changeProducts.php?id=2">LINE 2</a></li>
                    <li><a href="changeProducts.php?id=3">LINE 3</a></li>
                    <li><a href="changeProducts.php?id=4">LINE 4</a></li>
                </ul>
            </li>
            <?php 
                if ($permission_level != '2'){
            ?>
            <li>
                <a href="output_upload_form.php">Upload SMT Output</a>
            </li>
            <li>
                <a href="users.php">User management</a>
            </li>
            <?php
                }
            ?>
        </ul>
    </nav>
</div>

<script>
    $(document).ready(function() {
        $('nav ul li ').hover(
            function(){
                $('.fallback',this).show();
            },
            function(){
                $('.fallback',this).hide();
            }
        );
        
        /*$('.fallback li').hover(
            function(){
                $('.second-level',this).show();
            },
            function(){
                $('.second-level',this).hide();
            }
        );*/
    });
</script>
