<?php
/**
 * @author Caleb Milligan
 * Created 4/29/2018
 * @param string $class
 */
function dynamicCall(string $class) {
	$result = [
			"status" => 200,
			"success" => true,
			"message" => "OK"
	];
	if (!isset($_REQUEST["method"])) {
		$result["success"] = false;
		$result["message"] = "Required parameter \"method\" is missing";
	}
	else {
		$method = $_REQUEST["method"];
		try {
			$r = new ReflectionMethod($class, $method);
			if (!$r->isStatic() || $r->isPrivate()) {
				throw new ReflectionException();
			}
			
			$params = $r->getParameters();
			
			$values = array();
			
			foreach ($params as $param) {
				$name = $param->getName();
				$isArgumentGiven = array_key_exists($name, $_REQUEST);
				if (!$isArgumentGiven && !$param->isDefaultValueAvailable()) {
					$type = $param->hasType() ? $param->getType()->getName() : "mixed";
					$result["success"] = false;
					$result["message"] = "The required parameter " . $param->getName() . " ($type) was not defined";
					break;
				}
				$values[$param->getPosition()] = $isArgumentGiven ? $_REQUEST[$name] : $param->getDefaultValue();
			}
			if ($result["success"]) {
				try {
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
	print(json_encode($result));
	http_response_code($result["status"]);
}
