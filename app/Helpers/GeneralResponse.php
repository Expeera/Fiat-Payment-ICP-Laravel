<?php

function generalResponse($status , $message , $data , $status_code = 200) {
    $data = [
        'status' => $status ,
        'message' => $message ,
        'data' => $data ,
    ];
    return $data;
}
function responseJson($status , $message , $data , $status_code = 200) {
    return response()->json(generalResponse($status , $message , $data , $status_code) , $status_code);
}
