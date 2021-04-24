<?php

function output_redirect($uri, $sparql, $options, $reconstructed_uri, $unresolved_prefixes)
{
  $uri['query'] = get_query_string(create_query_array($sparql, $options));
  $target_uri = unparse_url($uri);
  http_response_code(303);
  header("Location: $target_uri");
}

function output_print($uri, $sparql, $options, $reconstructed_uri, $unresolved_prefixes)
{
  $target_uri = unparse_url($uri);
  $reconstructed_uri = unparse_url($reconstructed_uri);
  
  header('Content-Type: application/sparql-query');
  header('Content-Disposition: inline; filename="query.sparql"');
  echo "# Generated from $reconstructed_uri\n";
  echo "# This query would be sent to $target_uri\n\n";
  echo $sparql;
}

function output_debug($uri, $sparql, $options, $reconstructed_uri, $unresolved_prefixes)
{
  $target_uri = unparse_url($uri);
  $reconstructed_uri = unparse_url($reconstructed_uri);
  
  $sparql = htmlspecialchars($sparql);
  $inputs = create_query_array(null, $options);
  
  $target_uri = htmlspecialchars($target_uri);
  $reconstructed_uri = htmlspecialchars($reconstructed_uri);
  
  unset($uri['query']);
  $endpoint_uri = htmlspecialchars(unparse_url($uri));
  
  ?><!DOCTYPE html>
<html lang="en">
<head>
<title>lid: resolver</title>
<base href="/lid/">
<link rel="stylesheet" href="//is4.site/styles/terminal.css?theme=4">
<link rel="stylesheet" href="prism.css">
</head>
<body>
<pre><code class="language-sparql"><?php

  echo "# Generated from $reconstructed_uri\n";
  echo "# This query would be sent to $target_uri\n\n";
  echo $sparql;

?></code></pre>
<script src="prism.js"></script>
<p style="float:left"><a href=".">Back to the main page.</a></p>
<div style="float:right">
<form style="display:inline" method="GET" action="<?=$endpoint_uri?>">
<?php

  foreach($inputs as $key => $value)
  {
    ?><input type="hidden" name="<?=htmlspecialchars($key)?>" value="<?=htmlspecialchars($value)?>">
<?php
  }

?>
<textarea name="query" hidden style="display:none"><?=$sparql?></textarea>
<input type="submit" value="Send">
</form>
<form style="display:inline" method="GET" action="<?=$endpoint_uri?>">
<?php

  foreach($inputs as $key => $value)
  {
    if($key === 'explain') continue;
    ?><input type="hidden" name="<?=htmlspecialchars($key)?>" value="<?=htmlspecialchars($value)?>">
<?php
  }

?>
<input type="hidden" name="explain" value="on">
<textarea name="query" hidden style="display:none"><?=$sparql?></textarea>
<input type="submit" value="Analyze">
</form>
<form style="display:inline" method="POST" action="http://www.sparql.org/validate/query">
<textarea name="query" hidden style="display:none"><?php

  if(count($unresolved_prefixes) > 0)
  {
    echo "# These prefixes are supposed to be resolved by the target endpoint:\n";
    foreach($unresolved_prefixes as $prefix => $_)
    {
      $prefix = htmlspecialchars($prefix);
      echo "PREFIX $prefix: <$prefix#>\n";
    }
    echo "\n";
  }
  echo $sparql;

?></textarea>
<input type="hidden" name="languageSyntax" value="SPARQL">
<input type="hidden" name="outputFormat" value="sparql">
<input type="submit" value="Validate">
</form>
</div>
</body>
</html><?php
}