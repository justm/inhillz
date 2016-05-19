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

    public static $numInputs = 6;
    public static $numOutputs = 1;
    public static $numHidden = 2;
    public static $numNeuronsPerLayer = 15;

    public static $activationFunction = ['sigmoid', 'sigmoid', 'linear'];
    
    //for tweeking the sigmoid function
    public static $sigmoidActivationResponse = 1;
    
    //bias value
    public static $bias = -1;
    
    public static $numEpochs = 200;
    public static $learnRate = 0.01;
    
    public static $mseTarget = 0.0000002;
    
    //---------------------------------------GA parameters
//    public static $dCrossoverRate = 0.7;
//    public static $dMutationRate = 0.1;
//
//    //the maximum amount the ga may mutate each weight by
//    public static $dMaxPerturbation = 0.3;
//
//    //used for elitism
//    public static $iNumElite = 4;
//    public static $iNumCopiesElite = 1;
}
