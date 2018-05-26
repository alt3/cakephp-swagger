<?php
if (!class_exists('Cake\Http\Exception\MethodNotAllowedException')) {
    class_alias(
        'Cake\Network\Exception\MethodNotAllowedException',
        'Cake\Http\Exception\MethodNotAllowedException'
    );
    class_alias(
        'Cake\Network\Exception\NotImplementedException',
        'Cake\Http\Exception\NotImplementedException'
    );
    class_alias(
        'Cake\Network\Exception\BadRequestException',
        'Cake\Http\Exception\BadRequestException'
    );
}