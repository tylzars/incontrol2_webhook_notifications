<!DOCTYPE html>
<html>
<!--<meta http-equiv="refresh" content="30">-->
<meta name="author" content="Tyler Zars">

<body>
    <p>HI!</p>

<?php 
date_default_timezone_set("America/New_York");


# Send data to discord as an encoded json
function postToDiscord($message, $webhook_func) {
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($message)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($webhook_func, false, $context);
}

# Make the array a string, borrowed from: https://stackoverflow.com/a/26582529
function array2string($data){
    $log_a = "";
    foreach ($data as $key => $value) {
        if(is_array($value))    
            $log_a .= array2string($value);
        else                    
            $log_a .= $key.": ".$value."\n";
    }
    return $log_a;
}

# Discord Webhook URL
$webhook = "YOUR_WEBHOOK_HERE"; 

# Get data from HTTP POST
$data4 = json_decode(file_get_contents('php://input'), true);


# If data hits, send the notification
if($data4) {

    $notification_name = $data4[0]["device_name"];
    
    foreach ($data4 as $data) {
        # Unset data from the JSON
        unset($data["org_id"]);
        unset($data["network_id"]);
    }
    
    # Set the names to be correct for each! new_name => old value
    foreach ($data4 as $key => $val) {
        # Create the empty list
        $newList = [];
        
        # Build the message modularly with whatever is sent
        if ($val['device_name']) {
            $newList['Device Name'] = $val['device_name'];
        } 
        if ($val['event_type']) {
            $newList['Event Type'] = $val['event_type'];
        } 
        if ($val['detail']) {
            $newList['Detailed'] = $val['detail'];
        } 
        if ($val['ssid']) {
            $newList['SSID'] = $val['ssid'];
        } 
        if ($val['client_name']) {
            $newList['Client Name'] = $val['client_name'];
        } 
        if ($val['client_mac']) {
            $newList['Client MAC'] = $val['client_mac'];
        } 
        if ($val['msgId']) {
            $newList['Message ID'] = $val['msgId'];
        } 
        if ($val['longitude']) {
            $newList['Longitude'] = $val['longitude'];
        } 
        if ($val['latitude']) {
            $newList['Latitude'] = $val['latitude'];
        } 
        if ($val['ts']) {
            $newList['Time'] = $val['ts'];
        } 
        if ($val['gps_timestamp']) {
            $newList['GPS Timestamp'] = $val['gps_timestamp'];
        } 
        if ($val['ts']) {
            $newList['Serial Number'] = $val['sn'];
        } 
        
        # No PepVPN events in the important channel unless it is a disconnect
        if ($newList['Event Type'] == "PepVPN") {
            if (str_contains($newList['Detailed'], "disconnected")) {
                # Post to discord
                $stuff = array2string($newList);
                $discord_content = ['content' => $stuff,
                                    'username'=> $notification_name];
                postToDiscord($discord_content, $webhook);
                
                # Reset the list so it don't dupe on the second element of array
                $newList = [];
            }
        } else {
            # Post to discord
            $stuff = array2string($newList);
            $discord_content = ['content' => $stuff,
                                'username'=> $notification_name];
            postToDiscord($discord_content, $webhook);
            
            # Reset the list so it doesn't dupe on the second element of array
            $newList = [];
        }
    }
}

?>

</body>
</html>
