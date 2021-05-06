<?php
  // Change googleSpreadsheetUrl with your spreadsheet URL
	$googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/e/2PACX-1vRbHm3nV_JsJOJ4jAbKczdpGqLlSAbQ6Xa0Qz6pdxDBPLP11o0BD3WRSCttINOG4LfVldRRqhP2-4wT/pub?gid=0&single=true&output=csv";

  $rowCount = 0;
  $features = array();
  $error = FALSE;
  $output = array();
  $i = 1;

  // attempt to set the socket timeout, if it fails then echo an error
  if ( ! ini_set('default_socket_timeout', 15))
  {
    $output = array('error' => 'Unable to Change PHP Socket Timeout');
    $error = TRUE;
  } // end if, set socket timeout

  // if the opening the CSV file handler does not fail
  if ( !$error && (($handle = fopen($googleSpreadsheetUrl, "r")) !== FALSE) )
  {
    // while CSV has data, read up to 10000 rows
    while (($csvRow = fgetcsv($handle, 10000, ",")) !== FALSE)
    {
      $rowCount++;

      if ($rowCount == 1) { continue; } // skip the first/header row of the CSV

      // Split latitude longitude
      $coord = preg_split('/[\s,]+/', $csvRow[5]);

      $features[] = array(
        'type'     => 'Feature',
        'geometry' => array(
          'type'   => 'Point',
          'coordinates' => array(
            (float) $coord[1], // longitude, casted to type float
            (float) $coord[0] // latitude, casted to type float
          )
        ),
        'properties' => array(
          'id' => $i++,
          'namaobjek' => $csvRow[3],
          'keterangan' => $csvRow[4],
          'tanggalinput' => $csvRow[2],
        )
      );
    } // end while, loop through CSV data

    fclose($handle); // close the CSV file handler

    $output = array(
      'type' => 'FeatureCollection',
      'features' => $features
    );
  }  // end if , read file handler opened

  // else, file didn't open for reading
  else
  {
    $output = array('error' => 'Problem Reading Google CSV');
  }  // end else, file open fail

  // convert the PHP output array to JSON
  $jsonOutput = json_encode($output, JSON_NUMERIC_CHECK);

  // render JSON and no cache headers
  header('Content-type: application/json; charset=utf-8');
  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Access-Control-Allow-Origin: *');

  print $jsonOutput;
?>