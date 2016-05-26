<?php

namespace inhillz\components\ann;

/**
 * Neural net params
 * 
 * @namespace  inhillz\components\ann; 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0.0
 */
class Params {

    public static $numInputs = 5;
    public static $numOutputs = 1;
    public static $numHidden = 2;
    public static $numNeuronsPerLayer = 10;

    public static $activationFunction = ['tanh', 'tanh', 'linear'];
    
    public static $sigmoidActivationResponse = 1;
    
    public static $bias = -1;
    
    public static $numEpochs = 1000;
    public static $learnRate = 0.000001;
    
    public static $mseTarget = 0.02;
}
