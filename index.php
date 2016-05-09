<!DOCTYPE html>

<html>
<head>
  <title>/r/RandomActsOfGaming giveaway decider</title>
  <link rel='stylesheet' href='style.css' />
</head>

<body>
  <h1>/r/RandomActsOfGaming giveaway decider</h1>
  <form method="POST" action='index.php'>
    <label>Enter the bit after /comments/ in the URL of your giveaway, e.g. 3ab4cd: <input name='submissionID' type='text' value='<?php echo strip_tags($_POST["submissionID"]); ?>' placeholder="e.g. 3ab4cd" /></label><br />
    <label>Enter the upper bound of your random number (leave blank to choose random comment): <input name='upperBound' type='text' value='<?php echo strip_tags($_POST["upperBound"]); ?>' /></label><br />
    <label>Enter text required in comment (leave blank to accept any text): <input name='textToFind' type='text' value='<?php echo strip_tags($_POST["textToFind"]); ?>' /></label>
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
        $textToFind = strip_tags($_POST['textToFind']);
        $randNumber = rand(1, $upperBound);
        
        for ($i=0;$i<count($comments[1]["data"]["children"]);$i++){
          $commentText = strip_tags(html_entity_decode($comments[1]["data"]["children"][$i]["data"]["body_html"]));
          $commentWords = preg_replace("/[^a-zA-Z0-9]+/", "", explode(" ", $commentText));
          $number = "";
          foreach($commentWords as $commentWord){
            if (is_numeric($commentWord)){
              $number = intval($commentWord);
              break;
            }
          }
          if ($textToFind != ""){ // is there certain text to find in the comment?
            $alphanumeric = preg_replace('/[^a-zA-Z0-9\s]/', "", $commentText);
            if (strpos(strtolower($alphanumeric), strtolower($textToFind)) !== FALSE){ // text exists in comment, add to parsed
              $commentsParsed[] = [strip_tags(html_entity_decode($comments[1]["data"]["children"][$i]["data"]["body_html"])), $comments[1]["data"]["children"][$i]["data"]["author"], $number];
            }
          } else{
            $commentsParsed[] = [strip_tags(html_entity_decode($comments[1]["data"]["children"][$i]["data"]["body_html"])), $comments[1]["data"]["children"][$i]["data"]["author"], $number];
          }
          // ["text", "author", "number"]
        }
		    $winners = []; // prox, username, body text, spot on
        foreach ($commentsParsed as $comment){
          //echo("checking comment: ".$comment[0].", prox = ".abs($comment[2] - $randNumber).)
          if (count($winners) == 0){
            if ($comment[2] == $randNumber) $spotOn = true;
            else $spotOn = false;
            
            $winners[] = [abs($comment[2] - $randNumber), $comment[1], $comment[0], $spotOn]; // no winners yet, add first one
          } else{
            if (abs($comment[2] - $randNumber) < $winners[0][0]){ // more of a winner?
              if ($comment[2] == $randNumber) $spotOn = true;
              else $spotOn = false;
              
              $winners = []; // clear current list
              $winners[] = [abs($comment[2] - $randNumber), $comment[1], $comment[0], $spotOn]; // add to new list
            } else if (abs($comment[2] - $randNumber) == $winners[0][0]){
              if ($comment[2] == $randNumber) $spotOn = true;
              else $spotOn = false;
              
              $winners[] = [abs($comment[2] - $randNumber), $comment[1], $comment[0], $spotOn]; // add to current winners list
            }
          }
        }
        $commentCount = count($comments[1]["data"]["children"]);
        
        if (count($winners) > 1){
          $winner = $winners[array_rand($winners)];
          //echo "<p>".json_encode($winner)."</p>";
          $winnerUname = $winner[1];
          $winnerBody = $winner[2];
          $hadToDecide = true;
          $winnerSpotOn = $winners[3];
        } else{
          $winnerUname = $winners[0][1];
          $winnerBody = $winners[0][2];
          $winnerSpotOn = $winners[0][3];
        }
        
        if ($winnerSpotOn) $spotOn = " was spot on";
        else $closest = "The closest person was ";
        
        echo "<p>The number is $randNumber</p>";
        
        if ($hadToDecide) echo "<p>".count($winners)." people were the same proximity to the number, so one was randomly chosen</p>";
        
        echo "<p>$closest/u/$winnerUname$spotOn with this text: </p>";
        
        echo "<p class='quote'>\"$winnerBody\"</p>";
        
        echo "<p><a href='https://www.reddit.com/message/compose/?to=$winnerUname' target='_blank'>Tell them about it now!</a></p>";
        
        echo "<br />Reload the page to get another winner!<br />There are $commentCount comments total (if this doesn't match with the reddit comment count, it means some comments have been deleted).";
      } else if ($_POST['upperbound'] == ""){
        $textToFind = strip_tags($_POST['textToFind']);
        if ($textToFind != ""){
          $allComments = $comments[1]["data"]["children"];
          $chosenComments = [];
          foreach ($allComments as $comment){
            if (strpos(strtolower(preg_replace('/[^a-zA-Z0-9\s]/', "", strip_tags(html_entity_decode($comment['data']['body_html'])))), strtolower($textToFind)) !== False){
              $chosenComments[] = [strip_tags(html_entity_decode($comment['data']['body_html'])), $comment['data']['author']];
            }
          }
          $randNum = rand(0,count($chosenComments)-1);
          $bodyText = $chosenComments[$randNum][0];
          $author = $chosenComments[$randNum][1];
        } else{
          $randNum = rand(0,count($comments[1]["data"]["children"])-1);
          $bodyText = strip_tags(html_entity_decode($comments[1]["data"]["children"][$randNum]["data"]["body_html"]));
          $author = strip_tags(html_entity_decode($comments[1]["data"]["children"][$randNum]["data"]["author"]));
        }
        echo "<p>The chosen person was /u/$author with this text: </p>";
        
        echo "<p class='quote'>\"$bodyText\"</p>";
        
        echo "<p><a href='https://www.reddit.com/message/compose/?to=$author' target='_blank'>Tell them about it now!</a></p>";
	    }
    }
  ?>
  <p><br /></p>
  <hr />
  <p style="font-size: 8pt; color: grey;">This tool is completely open source - <a href="http://github.com/jthistle/rag-web/">find the code here</a></p>
  <p style="font-size: 8pt; color: grey;">Enjoy using this tool? Please leave a link to it in your giveaway - it really helps others looking for something like it!</p>
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