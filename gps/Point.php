<?php

class Point {
	private $x;
	private $y;
	
	public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
	}

	public function getX() {
		return $this->x;
	}

	public function setX($x) {
		$this->x = $x;
	}

	public function getY() {
		return $this->y;
	}

	public function setY($y) {
		$this->y = $y;
	}

	/**
	 * Calculates the cross product of two vectors
	 * 
	 * @return float The cross product of the vectors
	 */
	public static function cross($a, $b) {
		return $a->getX() * $b->getY() - $b->getX() * $a->getY();
	}
	
	/**
	 * Check if the sides of the polygon intersect
	 * 
	 * @param polygon	Vertexes of the polygon
	 * 
	 * @return boolean 	True if sides of the polygon intersect, false otherwise
	 */
	public static function checkIntersection($polygon) {
		$size = count($polygon);
		if ($size > 2) {
			for ($i = 0; $i < $size; $i++) {
				$j = $i + 1;
				if ($j == $size) {
					$j = 0;
				}

				$A = $polygon[$i];
				$B = $polygon[$j];
				$AB = new Point($B->getX() - $A->getX(), $B->getY() - $A->getY());

				for ($k = $j; $k < $size; $k++) {
					$l = $k + 1;
					if ($l == $size) {
						$l = 0;
					}

					$C = $polygon[$k];
					$D = $polygon[$l];
					$CD = new Point($D->getX() - $C->getX(), $D->getY() - $C->getY());

					$cross = Point::cross($AB, $CD);
					if ($cross != 0) {
						$m = -(-$AB->getX() * $A->getY() + $AB->getX() * $C->getY() + $AB->getY() * $A->getX() - $AB->getY() * $C->getX()) / $cross;
						$n = -($A->getX() * $CD->getY() - $C->getX() * $CD->getY() - $CD->getX() * $A->getY() + $CD->getX() * $C->getY()) / $cross;
						
						if (0.000001 < $m && $m < 0.999999 && 0.000001 < $n && $n < 0.999999) {
							return true;
						}
					}	
				}
			}
		}

		return false;
	}

	/**
	 * Construct the convex hull of a polygon
	 * 
	 * @param polygon	Vertexes of the polygon
	 * 
	 * @return array 	Convex hull of the polygon (array of points)
	 */
	public static function monotoneChain($polygon) {
		usort($polygon, function ($a, $b) {
			return $a->getX() == $b->getX() ? $a->getY() - $b->getY() : $a->getX() - $b->getX();
		});

		$upper_hulls = $lower_hulls = [];

		foreach ($polygon as $coord) {
			while (count($last_two = array_slice($lower_hulls, -2)) >= 2 && Point::cross(
				new Point($last_two[1]->getX() - $last_two[0]->getX(), $last_two[1]->getY() - $last_two[0]->getY()),
				new Point($coord->getX() - $last_two[0]->getX(), $coord->getY() - $last_two[0]->getY())) <= 0) {
				array_pop($lower_hulls);
			}
			$lower_hulls[] = $coord;
		}

		foreach (array_reverse($polygon) as $coord) {
			while (count($last_two = array_slice($upper_hulls, -2)) >= 2 && Point::cross(
				new Point($last_two[1]->getX() - $last_two[0]->getX(), $last_two[1]->getY() - $last_two[0]->getY()),
				new Point($coord->getX() - $last_two[0]->getX(), $coord->getY() - $last_two[0]->getY())) <= 0) {
				array_pop($upper_hulls);
			}
			$upper_hulls[] = $coord;
		}

		array_pop($upper_hulls);
		array_pop($lower_hulls);

		return array_merge($upper_hulls, $lower_hulls);
	}

	/**
	 * Check if the point is inside a polygon
	 * 
	 * @param polygon	Vertexes of the polygon
	 * 
	 * @return boolean 	True if the point is inside the polygon, False otherwise
	 */
	public function rayCasting($polygon) {
		$cpt = 0;
        $epsilon = 0.00001;
        $size = count($polygon);
		
		for ($i = 0; $i < $size; $i++) {
			$j = $i + 1;
			if ($j == $size) {
				$j = 0;
			}
			
			if ($polygon[$i]->getY() > $polygon[$j]->getY()) {
				$A = $polygon[$j];
				$B = $polygon[$i];
			} else {
				$A = $polygon[$i];
				$B = $polygon[$j];
			}
			
			if ($this->y == $A->getY() || $this->y == $B->getY()) {
				$this->y += $epsilon;
			}
			
			if ($this->y < $A->getY() || $this->y > $B->getY() || $this->x > max($A->getX(), $B->getX())) {
				continue;
			}
			
			if ($this->x < min($A->getX(), $B->getX())) {
				$cpt++;
				continue;
			}
			
			try {
				if ((($B->getY() - $A->getY()) / ($B->getX() - $A->getX())) <= (($this->y - $A->getY()) / ($this->x - $A->getX()))) {
					$cpt++;
					continue;
				}
			} catch (Exception $e) {
				continue;
			}
		}

		return ($cpt % 2) == 1;
    }
    
    private function max($a, $b) {
        if ($a > $b) {
            return $a;
        }

        return $b;
    }

    private function min($a, $b) {
        if ($a < $b) {
            return $a;
        }

        return $b;
	}
	
	/**
	 * Check if a position is inside an area
	 * 
	 * @param position	Position of the point to check
	 * @param area		Area to check
	 * 
	 * @return boolean 	True if the position is in the area, False otherwise
	 */
	public static function checkPosition($position, $area) {
		if (Point::checkIntersection($area)) {
			$area = Point::monotoneChain($area);
		}

		return $position->rayCasting($area);
	}
}
