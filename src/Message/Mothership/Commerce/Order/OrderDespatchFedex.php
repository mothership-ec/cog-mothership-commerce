<?php

class OrderDespatchFedex extends OrderDespatch
{
	protected function setType()
	{
		$this->typeID = 3;
	}

	public function getTrackingLink()
	{
		if ($this->code) {
			$link->text = 'Track this package (ref:' . $this->code . ') at Fedex.com';
			$link->href = 'http://fedex.com/Tracking?tracknumbers=' . $this->code;
			$link->ref  = $this->code;
			return $link;
		}
	}

	/**
	 * Get the label data as an array, where each element is a new line.
	 *
	 * A line break is added to the end of each line, because the label printer
	 * requires a line break at the end of each command sent.
	 *
	 * @return array The label data
	 */
	public function getPrintableLabelData()
	{
		$data = explode("\n", $this->labelData);

		foreach ($data as $key => $val) {
			$data[$key] .= "\n";
		}

		return $data;
	}
}