<?php

namespace Entity;

class Hit extends Base
{
	public function getX()
	{
		return $this->data['x'];
	}

	public function getY()
	{
		return $this->data['y'];
	}

	public function getPosition()
	{
		return ['x' => $this->getX(), 'y' => $this->getY()];
	}
}
