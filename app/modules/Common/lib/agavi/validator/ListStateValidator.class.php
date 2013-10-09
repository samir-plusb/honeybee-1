<?php

class ListStateValidator extends AgaviValidator
{
    protected static $childArgValidators = array(
        'offset' => array(
            'class' => 'AgaviNumberValidator',
            'min' => 0,
            'max' => 2147483647,
            'type' => 'integer'
        ),
        'limit' => array(
            'class' => 'AgaviNumberValidator',
            'min' => 0,
            'max' => 2147483647,
            'type' => 'integer'
        ),
        'search' => array(
            'class' => 'AgaviStringValidator',
            'min' => 1
        ),
        'filter' => array(
            'class' => 'Honeybee\Agavi\Validator\ArrayValidator'
        ),
        'field' => array(
            'class' => 'AgaviStringValidator',
            'base' => 'sorting',
            'min' => 1
        ),
        'referenceField' => array(
            'class' => 'AgaviStringValidator',
            'required' => false
        ),
        'referenceFieldId' => array(
            'class' => 'AgaviStringValidator',
            'required' => false
        ),
        'referenceModule' => array(
            'class' => 'AgaviStringValidator',
            'required' => false
        ),
        'direction' => array(
            'class' => 'AgaviInarrayValidator',
            'base' => 'sorting',
            'values' => array('asc', 'desc'),
            'strict' => true,
            'case' => true
        )
    );

    protected function validate()
    {
        $argument = $this->getArgument();
        $success = TRUE;
        $state = NULL;

        if ($argument && ($state = $this->getData($this->getArgument())))
        {
            if (! $state instanceof IListState)
            {
                $this->throwError('type');
                $success = FALSE;
            }
            // @todo add support for passing strings as a state,
            // thereby using a given string as a key to look up persisted list states.
            // this is useful for stuff like custom user filters etc.
        }
        
        if (! $state || ! $success)
        {
            if (($success = $this->validateChildArguments()))
            {
                $state = ListState::create($this->getChildArgumentsData());
            }
        }

        if ($state && $success)
        {
            $this->export($state, 'state');
        }

        return $success;
    }

    protected function validateChildArguments()
    {
        $success = TRUE;

        foreach ($this->createChildValidators() as $childValidator)
        {
            $childValidator->setParentContainer($this->getParentContainer());
            if (AgaviValidator::SILENT < $childValidator->execute($this->validationParameters))
            {
                $this->throwError($childValidator->getArgument());
                $success = FALSE;
            }
        }

        return $success;
    }

    protected function createChildValidators()
    {
        $validators = array();

        foreach (self::$childArgValidators as $argument => $validatorDef)
        {
            $validator = new $validatorDef['class'];

            $validatorDef['required'] = isset($validatorDef['required']) 
                ? $validatorDef['required'] 
                : FALSE;

            $validatorDef['name'] = sprintf(
                '_invalid_list_%s', isset($validatorDef['base']) 
                    ? $validatorDef['base'].'_'.$argument 
                    : $argument
            );

            $validator->initialize(
                $this->getContext(), 
                array_merge($this->getParameters(), $validatorDef),
                array($argument)
            );

            $validators[] = $validator;
        }

        return $validators;
    }

    protected function getChildArgumentsData()
    {
        $data = array();

        foreach (self::$childArgValidators as $argument => $validatorDef)
        {
            $childArgument = isset($validatorDef['base']) ? $validatorDef['base'] : $argument;
            if (! isset($data[$childArgument]))
            {
                if (NULL !== ($value = $this->getData($childArgument)))
                {
                    $data[$childArgument] = $value;
                }
            }
        }
        // atm we need to map the incoming sorting structure, to the ListState's prop names.
        $sorting = isset($data['sorting']) ? $data['sorting'] : array();
        if (isset($sorting['direction']))
        {
            $data['sortDirection'] = $sorting['direction'];
        }
        if (isset($sorting['field']))
        {
            $data['sortField'] = $sorting['field'];
        }

        return $data;
    }
}
