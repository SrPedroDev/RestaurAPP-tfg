<?php
// src/Validator/TelefonoMovilValidator.php
namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Doctrine\ORM\Query\AST\Functions\LengthFunction;
use Symfony\Component\Validator\Constraints\Length;




class DniValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Dni) {
            throw new UnexpectedTypeException($constraint, Dni::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        
        if (preg_match('/^\d{8}[A-Za-z]$/',  $value)) {
            // Separar los números y la letra
            $numero = substr( $value, 0, 8);
            $letra = substr( $value, 8, 1);
    
            // Array con las letras posibles y su índice asociado a los números
            $letras = "TRWAGMYFPDXBNJZSQVHLCKET";
            
            // Calcular la letra correcta según el número
            $letraCalculada = $letras[$numero % 23];
            
            // Comparar la letra proporcionada con la calculada
            if ($letra == $letraCalculada) {
                $valido =  true; // DNI válido
            } else {
                $valido = false; // Letra incorrecta
            }
        } else {
            $valido = false; // Formato incorrecto
        }
        if (!$valido  ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}



