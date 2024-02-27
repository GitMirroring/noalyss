<?php

/*
 *   This file is part of noalyss.
 *
 *   noalyss is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   noalyss is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Copyright Author Dany De Bontridder danydb@aevalys.eu 2002-2024

/*!\file
 * \brief 
 */
/*!
 *
 * \class  
 *
 * \brief 
 */

namespace Noalyss;

class File_Cache
{
    /**
     * @brief Check if the file exists, if yes then read otherwise take it from the url,
     * @param $url url to the file to get the content
     * @param $filename fullpath to the filename of the cache
     * @param $time_cache_second valid time, if the cache is older , than reload it from the url
     * @return void
     */
    public static function get_content($url, $filename, $time_cache_second)
    {
        $content = null;
        if (file_exists($filename)) {
            $date_time = new \DateTime();
            $file_tmstamp = filemtime($filename);

            $delta = $date_time->getTimestamp() - $file_tmstamp;

            // if file too old , refresh it
            if ($delta > $time_cache_second) {
                $content = file_get_contents($url);
                $f_file = fopen($filename, "w+");
                if ( $f_file == false ) {
                    return $content;
                }
                fwrite($f_file, $content);
                fclose($f_file);
            } else {
                $content = file_get_contents($filename);
            }
        } else {
            $content = @file_get_contents($url);
            $f_file = fopen($filename, "w+");
            if ( $f_file == false ) {
                return $content;
            }
            fwrite($f_file, $content);
            fclose($f_file);

        }
        return $content;
    }
}