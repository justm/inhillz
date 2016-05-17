<?php

namespace inhillz\components\ann;

/**
 * Params
 * 
 * @namespace  inhillz\components\ann; 
 * @package    \app\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @copyright  2015 OrchidSphere
 * @link       http://orchidsphere.com/
 * @license    License here
 * @version    1.0.0
 */
class Params {

    public static $iNumInputs = 6;
    public static $iNumHidden = 1;
    public static $iNumNeuronsPerLayer = 8;
    public static $iNumOutputs = 1;

    //for tweeking the sigmoid function
    public static $dActivationResponse = 1;
    //bias value
    public static $dBias = -1;
    
    public static $numEpochs = 1000;
    public static $learnRate = 0.1;
    
    public static $mseTarget = 0.02;
    
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
