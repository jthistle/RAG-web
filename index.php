<!DOCTYPE html>

<html>
<head>
  <title>/r/RandomActsOfGaming giveaway decider</title>
  <link rel='stylesheet' href='style.css' />
</head>

<body>
  <h1>/r/RandomActsOfGaming giveaway decider</h1>
  <form method="POST" action='index.php'>
    <label>Enter the bit after /comments/ in the URL of your giveaway: <input name='submissionID' type='text' value='<?php strip_tags($_POST["submissionID"]); ?>' /></label><br />
    <label>Enter the upper bound of your random number: <input name='upperBound' type='text' value='<?php strip_tags($_POST["upperBound"]); ?>' /></label>
    <p></p><input type='submit' name='getWinner' value='Choose a winner!' />
  </form>
  <?php
    if (isset($_POST['getWinner'])){
      require_once("Phapper/src/phapper.php");
      $r = new \Phapper\Phapper; // initialize PHP wrapper
      
      // $r->setDebug(true);
      //$r->comment("3zbb8s", "testing");
      $subID = strip_tags($_POST['submissionID']);
      $comments = json_decode(json_encode($r->getComments($subID)), true);
      
      if ($comments != null && $_POST['upperBound'] > 0){
        // $comments[1]["data"]["children"] // these are replies to main post
        $commentsParsed = Array();
        $upperBound = intval(strip_tags($_POST['upperBound']));
        $randNumber = rand(1, $upperBound);
        
        for ($i=0;$i<count($comments[1]["data"]["children"]);$i++){
          $commentWords = $result = preg_replace("/[^a-zA-Z0-9]+/", "", explode(" ", strip_tags(html_entity_decode($comments[1]["data"]["children"][$i]["data"]["body_html"]))));
          $number = "";
          foreach($commentWords as $commentWord){
            if (is_numeric($commentWord)){
              $number = intval($commentWord);
              break;
            }
          }
          $commentsParsed[] = [strip_tags(html_entity_decode($comments[1]["data"]["children"][$i]["data"]["body_html"])), $comments[1]["data"]["children"][$i]["data"]["author"], $number];
          // ["text", "author", "number"]
        }
        $closestProximity = $upperBound*2;
        $closestUsername = "";
        $closestCommentText = "";
        foreach ($commentsParsed as $comment){
          //echo("checking comment: ".$comment[0].", prox = ".abs($comment[2] - $randNumber).)
          if (abs($comment[2] - $randNumber) < $closestProximity){
            $closestProximity = abs($comment[2] - $randNumber);
            $closestUsername = $comment[1];
            $closestCommentText = $comment[0];
          }
        }
        $commentCount = count($comments[1]["data"]["children"]);
        
        echo "<p>The number is $randNumber</p>";
        
        echo "<p>The closest person was /u/$closestUsername with this text: </p>";
        
        echo "<p class='quote'>\"$closestCommentText\"</p>";
        
        echo "<p><a href='https://www.reddit.com/message/compose/?to=$closestUsername' target='_blank'>Tell them about it now!</a></p>";
        
        echo "<br />Reload the page to get another winner!<br />There are $commentCount comments total (if this doesn't match with the reddit comment count, it means some comments have been deleted).";
      }
    }
  ?>
  
  <!-- Start of StatCounter Code for Default Guide -->
  <script type="text/javascript">
    //<![CDATA[
    var sc_project=10766464; 
    var sc_invisible=1; 
    var sc_security="49b3124c"; 
    var scJsHost = (("https:" == document.location.protocol) ?
    "https://secure." : "http://www.");
    document.write("<sc"+"ript type='text/javascript' src='" +
    scJsHost+
    "statcounter.com/counter/counter_xhtml.js'></"+"script>");
    //]]>
  </script>
  <noscript><div class="statcounter"><a title="shopify
  analytics tool" href="http://statcounter.com/shopify/"
  class="statcounter"><img class="statcounter"
  src="http://c.statcounter.com/10766464/0/49b3124c/1/"
  alt="shopify analytics tool" /></a></div></noscript>
  <!-- End of StatCounter Code for Default Guide -->
  
</body>
</html>