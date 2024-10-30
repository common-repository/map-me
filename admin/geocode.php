<?php
// function to geocode address, it will return false if unable to geocode address
function geocode( $address=null, $api_key=null ) {

    if (!$address) return;

    // url encode the address
    $address = urlencode( $address );

    // google map geocode api url
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$api_key;

    // get the json response
    $response_json = file_get_contents($url);

    // decode the json
    $response = json_decode($response_json, true);

    $output = [
        'latitude'          => null,
        'longitude'         => null,
        'formatted_address' => null,
        'error'             => null
    ];

    // response status will be 'OK', if able to geocode given address
    if ($response['status'] == 'OK') {

        // get the important data
        $latitude = $response['results'][0]['geometry']['location']['lat'];
        $longitude = $response['results'][0]['geometry']['location']['lng'];
        $formatted_address = $response['results'][0]['formatted_address'];

        // verify if data is complete
        if ($latitude && $longitude && $formatted_address) {

            $output = [
                'latitude'          => $latitude,
                'longitude'         => $longitude,
                'formatted_address' => $formatted_address,
                'error'             => null
            ];

            return $output;

        } else {
            $output['error'] = true;
            return $output;
        }

    } else {
        $output['error'] = isset($response['error_message']) ? $response['error_message'] : $response['status'];
        return $output;
    }
}

?>
