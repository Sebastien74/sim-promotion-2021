<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
         <meta charset="utf-8" />
         <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
         <meta name="description" content="" />
         <meta name="author" content="" />
         <!-- Title -->
         <title>Sorry, This Page Can&#39;t Be Accessed</title>
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />
         <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous" />
    </head>

    <body class="bg-dark text-white">
        <div class="container text-center">
            <div class="row" style="min-height: 100vh;">
                <div class="col-12 my-auto">
                    <div class="text-center text-danger">
                        <p><i class="fa fa-exclamation-triangle fa-5x"></i><br/>Status Code: 403</p>
                    </div>
                    <h3>OPPSSS!!!!</h3>
                    <?php if(!empty($_GET['site'])) : ?>
                        <p>You are not allowed to edit this website !! <br/> <strong>Ask to webmaster</strong></p>
                    <?php else : ?>
                        <p>Sorry, your access is refused due to security reasons of our server and also our sensitive data.<br/>Please go back to the previous page to continue browsing.</p>
                    <?php endif; ?>
                    <div id="footer" class="text-center">
                        <i class="fa fa-copyright mr-1"></i>Agence Félix <?= date("Y"); ?>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>