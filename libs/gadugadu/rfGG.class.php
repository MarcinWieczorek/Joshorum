<?php
	const PONG 				= 0x0007; //Pong
		if (false === ($aServer = $this->_findServer($uid))) {
		if (! $this->_writePacket(self::LOGIN80, $this->_packLogin80($uid, $password, $seed, $status, $description)) 
		if (!$this->_writePacket(self::LIST_EMPTY) || !$data = $this->_read(self::NOTIFY_REPLY80, true)) {

	public function changeStatus($status, $statusDescription = null) {
	 */
		} else {

		$this->_read(self::PING);
	 * Jesli serwer nie odpoie funkcja bedzie czekac przez chwile (5 sek jesli nie zostalo to zmienione)
	public function pong() {
		$this->changeStatus(self::STATUS_NOT_AVAIL, $statusDescription);			
	}
	 
	protected function _fontFormat($string) {
		$fontFormat = array();
		$a2 = array();
					$a1 |= self::FONT_COLOR;
				$d += strlen($cFontData[0]);
		$b = '';

?>