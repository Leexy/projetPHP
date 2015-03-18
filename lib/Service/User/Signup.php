<?php
namespace Service\User;

use Repository\UserRepository;
use Repository\Error\Base as RepositoryError;
use Repository\Error\DuplicatedKey;

class Signup
{
  const ERROR_NO_DATA = 10;
  const ERROR_INVALID_EMAIL = 20;
  const ERROR_BAD_CONFIRMATION = 30;
  const ERROR_EMAIL_ALREADY_USED = 40;
  const ERROR_QUERY_FAILED = 50;

  /**
   * @var UserRepository
   */
  private $userRepository;

  /**
   * @var string
   */
  private $error = null;

  /**
   * @var string
   */
  private $email;

  /**
   * @var string
   */
  private $password;

  /**
   * @var string
   */
  private $passwordConfirmation;

  public function proceed()
  {
    if (!strlen($this->email) or !strlen($this->password)) {
      $this->error = self::ERROR_NO_DATA;
    } else if (!$this->isEmailValid()) {
      $this->error = self::ERROR_INVALID_EMAIL;
    } else if ($this->password !== $this->passwordConfirmation) {
      $this->error = self::ERROR_BAD_CONFIRMATION;
    } else {
      try {
        $this->createUser();
      } catch (DuplicatedKey $error) {
        $this->error = $error->getKey() === 'email' ?
          self::ERROR_EMAIL_ALREADY_USED :
          self::ERROR_QUERY_FAILED;
      } catch (RepositoryError $error) {
        $this->error = self::ERROR_QUERY_FAILED;
      }
    }
  }

  private function createUser()
  {
    $this->userRepository->create($this->email, $this->password);
  }

  /**
   * @return bool
   */
  private function isEmailValid()
  {
    return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
  }

  /**
   * @param UserRepository $userRepository
   */
  public function setUserRepository($userRepository)
  {
    $this->userRepository = $userRepository;
  }

  /**
   * @param string $email
   */
  public function setEmail($email)
  {
    $this->email = $email;
  }

  /**
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }

  /**
   * @param string $passwordConfirmation
   */
  public function setPasswordConfirmation($passwordConfirmation)
  {
    $this->passwordConfirmation = $passwordConfirmation;
  }

  /**
   * @return string
   */
  public function getError()
  {
    return $this->error;
  }

  /**
   * @return bool
   */
  public function hasError()
  {
    return $this->error !== null;
  }
}
