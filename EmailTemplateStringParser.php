<?php

class EmailTemplateStringParser {

    protected string $template;
    protected array $args;
    protected string $processed_string;
    protected ?array $cache = null;

    public function __construct(string $template, array $args)
    {
        $this->template = $this->processed_string = $template;
        $this->args = $args;
    }

    /** Start of parser functions
     * functions follow the format emailTemplateParserFunction{function name}
     * 
     * e.g. emailTemplateParserFunctionHelloWorld
     */
    public static function emailTemplateParserFunctionExampleFunction($search, $subject, $args){
        return str_replace($search, sprintf("User id is: %s", $args['companyuser']->id), $subject);
    }

    // TODO: No good way to handle =complex things except full parsing
    // public static function emailTemplateParserFunctionFor($search, $subject, $args){


    //     // $arg = array_shift($args);

    //     // if(!is_iterable($arg)){
    //     //     throw new Exception("Argument 1 given must be iterable");
    //     // }

    //     // // $string = array_shift($args);
    //     //  $string = implode(',', $args);
    //     //  $new_string = '';
    
    //     // //  while($new_string !== $string){
    //     // //     $new_args = [
    //     // //         '__loop__' => [
    //     // //             'index'
    //     // //         ]
    //     // //     ];

    //     // //     $new_string = self::parseTemplate($string, $args);
    //     // //     dd($new_string);
    //     // //  }

    //     return $subject;
    // }
    /** End of functions */ 

    /** End of lower order replacement functions **/

    /** Start of higher order replacement functions **/

    public static function traverseObjectRecursively($object, $keys){
        if(is_null($object) || count($keys) === 0){
            return $object;
        }

        $key = array_shift($keys);
        if(is_array($object)){
            return self::traverseObjectRecursively($object[$key] ?? null, $keys);   
        }

        if(is_object($object)){
            return self::traverseObjectRecursively($object->{$key} ?? null, $keys); 
        }
    }

    protected function replaceObjectPlaceholders($replacements)
    {
        foreach ($replacements as $replacement) {
            $param = trim($replacement, "{!}");
            $keys = explode('.', $param);
            $param = $keys[0];
            array_shift($keys);

            if(isset($this->args[$param])){
                $this->processed_string = str_replace(
                    $replacement, 
                    self::traverseObjectRecursively($this->args[$param], $keys),  
                    $this->processed_string
                );
            }
        }

        return $this->processed_string;
    }   
    
     protected function replaceDirectValuePlaceholders($replacements){
        foreach ($replacements as $replacement) {
            $param = trim($replacement, "{!}");

            if(isset($this->args[$param])){
                $this->processed_string = str_replace($replacement, $this->args[$param],  $this->processed_string);
            }
        }

        return $this->processed_string;
     } 

    /** End of higher order replacement functions **/

    /**
     * Functional replacements are used to fill out values from a given value
     * since they affect the template string structure.
     * 
     * For now we don't have any special functions for this
     * 
     * in future we might add new functions 
     */
    protected function processFunctionalReplacements(){
        preg_match_all('/{#[a-zA-Z0-9_]*:?[\s\S]*}+/m', $this->processed_string, $function_placeholders);
        $function_placeholders = $function_placeholders[0] ?? [];

        foreach($function_placeholders as $function){
            $function_name = trim($function, '{#}');
            $temp = explode(":", $function_name);
            $function_name = $temp[0];
            array_shift($temp);
            $temp = implode(':', $temp);

            // Get functional arguments
            $args = explode(',', $temp);
            $args = array_filter($args, function($value) {
                return !empty($value);
            });

            $temp = [];

            foreach($args as $arg){
                $temp[$arg] = $this->args[$arg] ?? $arg;
            }
            
            // Set args
            $args = $temp;
            $args['__original_args__'] = $this->args;
            
            // Set function name
            $function_name = sprintf("emailTemplateParserFunction%s", $function_name);

            if(function_exists($function_name)){
                $this->processed_string = call_user_func($function_name, $function, $this->processed_string, $args);
            } else if(method_exists(self::class, $function_name)){
                $this->processed_string = call_user_func([self::class, $function_name], $function, $this->processed_string, $args);
            } else {
                throw new Exception(sprintf("Function %s not found", $function));
            }
        }
        
        // Recursively process so that it can handle nested function calls
        return $this->processed_string;
    }

    /**
     * Higher order replacements are the ones that come after
     * lower order replacements they don't affect the template string
     * instead they are simply used for one to one replacements
     * 
     * in future we might add new functions 
     */
    protected function processHigherOrderReplacements(){
        // Process object placeholders first
        preg_match_all('/{![a-zA-Z0-9_]*\.[a-zA-Z0-9_\-\.]*}+/m', $this->processed_string, $object_placeholders);
        $this->processed_string = $this->replaceObjectPlaceholders($object_placeholders[0] ?? []);
        
        // Process direct replacements
        preg_match_all('/{![a-zA-Z0-9_]*}+/m', $this->processed_string, $direct_values_placeholders);
        $this->processed_string = $this->replaceDirectValuePlaceholders($direct_values_placeholders[0] ?? []);

        return $this->processed_string;
    }

    public function parse(){
        // Start with lower order functions
        $this->processed_string = $this->processHigherOrderReplacements();
        $this->processed_string = $this->processFunctionalReplacements();

        return $this->processed_string;
    }

    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    public static function parseTemplate(string $template, array $args){
        return (new self($template, $args))->parse();
    }
    
}
