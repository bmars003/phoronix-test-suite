<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2011, Phoronix Media
	Copyright (C) 2008 - 2011, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class pts_test_result
{
	// Note in most pts-core code the initialized var is called $result_object
	// Note in pts-core code the initialized var is also called $test_run_request
	private $result;
	private $used_arguments;
	private $used_arguments_description;

	public $test_profile;
	public $test_result_buffer;

	public function __construct(&$test_profile)
	{
		$this->test_profile = $test_profile;
		$this->result = 0;
	}
	public function set_test_result_buffer($test_result_buffer)
	{
		$this->test_result_buffer = $test_result_buffer;
	}
	public function set_used_arguments_description($arguments_description)
	{
		$this->used_arguments_description = $arguments_description;
	}
	public function set_used_arguments($used_arguments)
	{
		$this->used_arguments = $used_arguments;
	}
	public function get_arguments()
	{
		return $this->used_arguments;
	}
	public function get_arguments_description()
	{
		return $this->used_arguments_description;
	}
	public function set_result($result)
	{
		$this->result = $result;
	}
	public function get_result()
	{
		return $this->result;
	}
	public function get_comparison_hash($show_version_and_attributes = true)
	{
		return $show_version_and_attributes ? pts_test_profile::generate_comparison_hash($this->test_profile->get_identifier(false), $this->get_arguments(), $this->get_arguments_description(), $this->test_profile->get_app_version()) : pts_test_profile::generate_comparison_hash($this->test_profile->get_identifier(false), $this->get_arguments());
	}
	public function __toString()
	{
		return $this->test_profile->get_identifier(false) . ' ' . $this->get_arguments() . ' ' . $this->get_arguments_description() . ' ' . $this->test_profile->get_override_values();
	}
	public function normalize_buffer_values()
	{
		if($this->test_profile->get_display_format() != 'BAR_GRAPH') // BAR_ANALYZE_GRAPH is currently unsupported
		{
			return;
		}

		$this->test_profile->set_result_proportion('HIB');
		$this->test_profile->set_result_scale('Relative Performance');
		$is_multi_way = pts_render::multi_way_identifier_check($this->test_result_buffer->get_identifiers());

		if($is_multi_way || true) // TODO: get multi-way working
		{
			$proportion = $this->test_profile->get_result_proportion();
			$all_values = $this->test_result_buffer->get_values();

			switch($proportion)
			{
				case 'HIB':
					$divide_value = min($all_values);
					break;
				case 'LIB':
					$divide_value = max($all_values);
					break;
			}

			unset($all_values);

			$buffer_items = $this->test_result_buffer->get_buffer_items();

			foreach($buffer_items as &$buffer_item)
			{
				/*
				$identifier_r = pts_strings::trim_explode(': ', $buffer_item->get_result_identifier());

				if(!isset($group_values[$identifier_r[1]]))
				{
					$group_values[$identifier_r[1]] = 0;
				}

				$group_values[$identifier_r[1]] += $buffer_item->get_result_value(); */

				switch($proportion)
				{
					case 'HIB':
						$value = $buffer_item->get_result_value() / $divide_value;
						break;
					case 'LIB':
						$value = $divide_value / $buffer_item->get_result_value();
						break;
				}

				$normalized = pts_math::set_precision(($buffer_item->get_result_value() / $divide_value), 2);
				$buffer_item->reset_result_value($normalized);
				$buffer_item->reset_raw_value(0);
			}

			$this->test_result_buffer = new pts_test_result_buffer($buffer_items);
		}
	}
}

?>
