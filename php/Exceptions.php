<?php
/**
 * @author Caleb Milligan
 * Created 3/26/2018
 */

class UserException extends Exception {

}

class UsernameInUseException extends UserException {

}

class EmailInUseException extends UserException {

}

class InvalidCredentialsException extends UserException {

}

class TrackException extends Exception {

}

class TrackExistsException extends TrackException {

}
