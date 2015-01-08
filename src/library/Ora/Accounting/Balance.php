<?php
namespace Ora\Accounting;

/**
 * 
 * @author andreabandera
 *
 */
class Balance  implements \Serializable {
	
	/**
	 * 
	 * @var float
	 */
	private $value;
	
	/**
	 * 
	 * @var DateTime
	 */
	private $date;
	
	public function __construct($value, \DateTime $date)
	{
		$this->value = $value;
		$this->date = $date;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function getDate()
	{
		return $this->date;
	}

	public function serialize()
	{
		$data = array(
			'value' => $this->value,
			'date' => $this->date,
		);
	    return serialize($data); 
	}
	
	public function unserialize($encodedData)
	{
	    $data = unserialize($encodedData);
	    $this->value = $data['value'];
	    $this->date = $data['date'];
	}
}