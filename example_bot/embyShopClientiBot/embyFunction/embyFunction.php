<?php

$embyServer = "https://em3np.apollocloud.me/emby";
$embyAPIKey = "134868f6bdbc4ef3b1f62c9eaf888135";

function create_user($email, $password = false, $isAdministrator = false) {
    global $embyServer, $embyAPIKey;
    
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "$embyServer/Users/New",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            "X-Emby-Token: $embyAPIKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode([
            "Name" => $email,
        ])
    ]);

    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($http_status != 200 || $response === false) {
        return false;
    } else {
        $responseData = json_decode($response, true);
        $userId = $responseData['Id'] ?? null;

        if($userId === null) 
            return false;
            
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$embyServer/Users/$userId/Password",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                "X-Emby-Token: $embyAPIKey",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "NewPw" => $password,
                "ResetPassword" => false
            ])
        ]);

        $passwordResponse = curl_exec($curl);
        $passwordHttpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$embyServer/Users/$userId/Policy",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "X-Emby-Token: $embyAPIKey",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "IsHidden" => true,
                "EnableSharedDeviceControl" => false,
                "EnableLiveTvManagement" => false,
                "EnableLiveTvAccess" => false,
                "RestrictedFeatures" => [
                "notifications"
                ],
                "EnableContentDownloading" => false,
                "EnableSubtitleDownloading" => false,
                "EnableSubtitleManagement" => false,
                "EnableSyncTranscoding" => false,
                "EnableMediaConversion" => false,
                "EnablePublicSharing" => false,
                "SimultaneousStreamLimit" => 2,
                "AllowCameraUpload" => false,
            ])
        ]);

        $responsePolicy = curl_exec($curl);
        $httpStatusPolicy = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($passwordHttpStatus != 204 || $passwordResponse === false || $httpStatusPolicy != 204 || $responsePolicy === false) {
            return false;
        } else {
            return $userId;
        }
    }
}


function create_password($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $password = '';
    for ($i = 0; $i < $length/2; $i++) {
        $password .= $characters[rand(0, $charactersLength - 1)];
    }
    $password .= '-';
    for ($i = 0; $i < $length/2; $i++) {
        $password .= $characters[rand(0, $charactersLength - 1)];
    }
    return $password;
}

function delete_user($userId) {
    global $embyServer, $embyAPIKey;

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "$embyServer/Users/$userId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "X-Emby-Token: $embyAPIKey",
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_status != 204 || $response === false) {
        return false;
    } else {
        return true;
    }

}

function invertiData($data) {
    $dt = DateTime::createFromFormat('Y-m-d', $data);
    if ($dt === false) return $data;
    return $dt->format('d/m/Y');
}