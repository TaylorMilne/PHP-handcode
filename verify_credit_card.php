<?php

 function luhn($number)
    {
        // Force the value to be a string as this method uses string functions.
        // Converting to an integer may pass PHP_INT_MAX and result in an error!
        $number = (string) $number;

        if ( ! ctype_digit($number))
        {
            // Luhn can only be used on numbers!
            return FALSE;
        }

        // Check number length
        $length = strlen($number);

        // Checksum of the card number
        $checksum = 0;

        for ($i = $length - 1; $i >= 0; $i -= 2)
        {
            // Add up every 2nd digit, starting from the right
            $checksum += substr($number, $i, 1);
        }

        for ($i = $length - 2; $i >= 0; $i -= 2)
        {
            // Add up every 2nd digit doubled, starting from the right
            $double = substr($number, $i, 1) * 2;

            // Subtract 9 from the double where value is greater than 10
            $checksum += ($double >= 10) ? ($double - 9) : $double;
        }

        // If the checksum is a multiple of 10, the number is valid
        return ($checksum % 10 === 0);
    }


 function validate_creditcard($number) {


        $card_array= array( 'default' => array(
            'length' => '13,14,15,16,17,18,19',
            'prefix' => '',
            'luhn'   => TRUE,
        ),
        'american express' => array(
            'length' => '15',
            'prefix' => '3[47]',
            'luhn'   => TRUE,
        ),
        'diners club' => array(
            'length' => '14,16',
            'prefix' => '36|55|30[0-5]',
            'luhn'   => TRUE,
        ),
        'discover' => array(
            'length' => '16',
            'prefix' => '6(?:5|011)',
            'luhn'   => TRUE,
        ),
        'jcb' => array(
            'length' => '15,16',
            'prefix' => '3|1800|2131',
            'luhn'   => TRUE,
        ),
        'maestro' => array(
            'length' => '16,18',
            'prefix' => '50(?:20|38)|6(?:304|759)',
            'luhn'   => TRUE,
        ),
        'mastercard' => array(
            'length' => '16',
            'prefix' => '5[1-5]',
            'luhn'   => TRUE,
        ),
        'visa' => array(
            'length' => '13,16',
            'prefix' => '4',
            'luhn'   => TRUE,
        ),
    );

     // Remove all non-digit characters from the number
        if (($number = preg_replace('/\D+/', '', $number)) === '')
            return FALSE;

        // Use the default type
        $type = 'default';  

        $cards = $card_array;

        // Check card type
        $type = strtolower($type);

        if ( ! isset($cards[$type]))
            return FALSE;

        // Check card number length
        $length = strlen($number);

        // Validate the card length by the card type
        if ( ! in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
            return FALSE;

        // Check card number prefix
        if ( ! preg_match('/^'.$cards[$type]['prefix'].'/', $number))
            return FALSE;

        // No Luhn check required
        if ($cards[$type]['luhn'] == FALSE)
            return TRUE;

        return luhn($number);
    }

?>