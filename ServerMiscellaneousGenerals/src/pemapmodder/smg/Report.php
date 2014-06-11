<?php

namespace pemapmodder\smg;

use pocketmine\Player;
use pocketmine\command\CommandSender as Issuer;
use pocketmine\Server;

class Report{
	/**
	 * the number of reports from the very first time the server started
	 * @var int
	 */
	public static $rid = 0;
	/**
	 * whether the report has been resolved
	 * @var bool
	 */
	protected $resolved = false;
	/**
	 * report read/unread status
	 * @var bool
	 */
	protected $read = false;
	/**
	 * @var Player $viewer
	 */
	protected $warning = 0;
	protected $viewer = null;
	protected $details;
	protected $backLog;
	protected $type;
	protected $reporter;
	protected $reported;
	protected $reportedIP;
	protected $reportedWorld;
	/**
	 * @param Player $player
	 * @param bool $type
	 * @param string $details
	 * @param Issuer $reporter
	 */
	public function __construct(Player $player, $type, $details, Issuer $reporter){
		$this->id = self::$rid++;
		$this->details = $details;
		$this->backLog = $type ? Main::get()->getActionLogger()->exportChatBacklog():Main::get()->getActionLogger()->exportMotionBacklog($player->getID());
		$this->type = $type;
		$this->reporter = $reporter->getName();
		$this->reported = $player->getName();
		$this->reportedIP = $player->getAddress();
		$this->reportedWorld = $player->getLevel()->getName();
	}
	public function isResloved(){
		return $this->resolved;
	}
	public function isRead(){
		return $this->read;
	}
	public function getID(){
		return $this->id;
	}
	public function getDetails(){
		return $this->details;
	}
	public function __toString(){
		$output = "";
		$output .= "Report reported by {$this->reporter}, ID {$this->id}:\n";
		$output .= "Type: ".($this->type ? "improper chat behavior":"using movement-related mods\n");
		$output .= "Reported player: {$this->player} (IP address {$this->reportedIP})\n";
		$output .= "Report details: {$this->details}\n";
		$output .= "Use /rvl {$this->id} for the backlog in the report";
		return $output;
	}
	public function getLog($page = 1){
		return "Showing page $page of report RID {$this->rid}:\n".implode("\n", array_slice(explode("\n", $this->backLog), ($page - 1) * 5 + 1, 5));
	}
	public function getPageNumber(){
		return (count(explode("\n", $this->backLog)) - 1) / 5 + 1;
	}
	public function resolve(){
		$this->resolved = true;
		$this->read = true;
	}
	public function read(){
		$this->read = true;
	}
	public function setViewer(Player $player){
		if(!Main::get()->hasPermission($player, $this->reportedWorld)){
			return false;
		}
		$this->viewer = $player;
		return true;
	}
	public function getViewer(){
		return $this->viewer;
	}
	public function warn($flags){
		$penalty = Penalty::add($this->viewer, Server::getInstance()->getOfflinePlayer($this->reported), $flags, 5, "via report approval");
		$this->warning = $penalty->getPoints();
		$this->resolved = true;
		$this->read = true;
	}
} 