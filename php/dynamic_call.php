<?php
/**
 * @author Caleb Milligan
 * Created 4/29/2018
 * @param string $class
 */
function dynamicCall(string $class) {
	// Set up the result array
	$result = [
			"status" => 200,
			"success" => true,
			"message" => "OK"
	];
	// Ensure the "method" parameter is set
	if (!isset($_REQUEST["method"])) {
		$result["success"] = false;
		$result["message"] = "Required parameter \"method\" is missing";
	}
	else {
		$method = $_REQUEST["method"];
		try {
			// Reflection magic
			$r = new ReflectionMethod($class, $method);
			// Ignore methods that are either private or not static
			if (!$r->isStatic() || $r->isPrivate()) {
				throw new ReflectionException();
			}
			
			// Ensure all required parameters are included
			$params = $r->getParameters();
			$values = array();
			foreach ($params as $param) {
				$name = $param->getName();
				$arg_present = array_key_exists($name, $_REQUEST);
				// Set error message if required parameter is not present
				if (!$arg_present && !$param->isDefaultValueAvailable()) {
					$type = $param->hasType() ? $param->getType()->getName() : "mixed";
					$result["success"] = false;
					$result["message"] = "The required parameter " . $param->getName() . " ($type) was not defined";
					break;
				}
				// Set the parameter in the array
				$values[$param->getPosition()] = $arg_present ? $_REQUEST[$name] : $param->getDefaultValue();
			}
			if ($result["success"]) {
				try {
					// Call the method
					$result["result"] = call_user_func_array(array($class, $method), $values);
				}
				catch (Throwable $e) {
					$result["success"] = false;
					$result["thrown"] = get_class($e);
				}
			}
		}
		catch (ReflectionException $e) {
			$result["success"] = false;
			$result["message"] = "The supplied method does not exist";
			error_log($e);
		}
	}
	// Print out the result as a JSON string
	print(json_encode($result));
	http_response_code($result["status"]);
}
