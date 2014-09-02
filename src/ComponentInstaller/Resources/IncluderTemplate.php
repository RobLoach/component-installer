<?php
	namespace ComponentInstaller;
	class Includer
	{
		/**
		 * This will hold the package information. The placeholder is between quotes
		 * so the template itself is still valid PHP.
		 */
		protected static $data = '{{packages}}';
		

		public static function getPath($packageName)
		{
			if (isset(self::$data[$packageName]))
			{
				return self::$data[$packageName]['path'];
			}
		}
		/**
		 * Gets the scripts for a package.
		 * Any globs are replaced by the individual file names.
		 * @param string $packageName
		 */
		public static function getScripts($packageName)
		{
			$path = self::getPath($packageName);
			$result = [];
			if (isset(self::$data[$packageName]['scripts']))
			{
				foreach (self::$data[$packageName]['scripts'] as $fileName)
				{
					$result[] = $path . DIRECTORY_SEPARATOR . $fileName;
				}
			}
			return $result;
		}

		/**
		 * Gets the styles for a package.
		 * Any globs are replaced by the individual file names.
		 * @param string $packageName
		 */
		public static function getStyles($packageName)
		{
			$path = self::getPath($packageName);
			$result = [];
			if (isset(self::$data[$packageName]['styles']))
			{
				foreach (self::$data[$packageName]['styles'] as $fileName)
				{
					$result[] = $path . DIRECTORY_SEPARATOR . $fileName;
				}
			}
			return $result;
		}

		/**
		 * Gets the base url for a package.
		 * @param string $packageName
		 */
		public static function getUrl($packageName)
		{
			if (isset(self::$data[$packageName]))
			{
				return self::$data[$packageName]['url'];
			}
		}
		/**
		 * Gets the files for a package.
		 * Any globs are replaced by the individual file names.
		 * @param string $packageName
		 */
		public static function getFiles($packageName)
		{
			$path = self::getPath($packageName);
			$result = [];
			if (isset(self::$data[$packageName]['files']))
			{
				foreach (self::$data[$packageName]['files'] as $fileName)
				{
					$result[] = $path . DIRECTORY_SEPARATOR . $fileName;
				}
			}
			return $result;
		}

		
	}

?>