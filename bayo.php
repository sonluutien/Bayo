<form method="post" action="">
    <input type="submit" name="create" value="create 1 million integer" /><a href="./unsort.txt">unsorted.txt</a>
</form>
<form method="post" action="">
    <input type="submit" name="sort" value="sort" /><a href="./sorted.txt">sorted.txt</a>
</form>
<?php
/**
 * Sort 1 million interger 32 bit use 3MB RAM
 * 
 * -----------------------------------------------------
 * Step 1:  read 10k number => sort and Store to temfile
 *      - with 10 mil => we have 100 file temp
 * 
 * Stept 2: we choose first line of 100 temp file => store to array
 * 
 * Step 3: find lowest element -> save to ouput
 * 
 * step 4: remove lowest element from array & file store it
 * 
 * step 5: get new element from lowest file
 * 
 * step 6:  back to step 3
 * 
 * 
 * 
 */


// no limit time execute
set_time_limit(0);

// limit memory use
ini_set('memory_limit', '3M');

$input = "unsort.txt";
$output = "sorted.txt";

if (isset($_POST['create'])) {
    make1mil($input);
}
if (isset($_POST['sort'])) {

    // read & sort => 100 file
    $listTemp = read10k($input);

    // min each file
    $min = -1;

    // sorted array
    $arrSorted = array();

    $k = -1;
    $arrElement = array();
    while (true) {
        for ($i = 0; $i < count($listTemp); $i++) {

            if ($k < 0) {
                $a = array();
                $a = file($listTemp[$i]);

                if (!empty($a[0]))
                    $arrElement[$i] = $a[0];
                unset($a);
                
                removeFirstLine($listTemp[$i]);
                
            } else {
                $a = array();
                $a = file($listTemp[$k]);
                
                if (!empty($a[0]))
                    $arrElement[$k] = $a[0];
                unset($a);
                
                removeFirstLine($listTemp[$k]);
                
                //var_dump($arrElement); die();
                
                break;
            }
        }

        // check array empty
        $errors = array_filter($arrElement);
        if (empty($errors))
            break;

        // compare found min
        $min = intval($arrElement[0]);
        //if($min ==2) echo("222");
        $k = 0;
        for ($j = 1; $j < 100; $j++) {
            if ($min > intval($arrElement[$j])) {
                $k = $j;
            }
        }
        $min = intval($arrElement[$k]);
        $arrElement[$k] = "";
        

        // write output
        write((int)$min, $output, "\n");
    }

}


function removeFirstLine($file)
{
    $contents = null;
    $contents = file($file, FILE_SKIP_EMPTY_LINES);
    
    $f = fopen($file, 'w');
    fwrite($f, join("", array_splice($contents, 1)));
    fclose($f);

    unset($contents);

}


/**
 * read 10k number and sort
 * write to file
 */
function read10k($dir)
{
    $listTemp = array();

    $file = fopen($dir, "r");

    $arr = array();
    while (!feof($file)) {

        $arr[] = fgets($file);

        if (count($arr) == 10000) {
            // write to temp file
            $temp = tempnam("/tmp/", "tmpfile");
            $listTemp[] = $temp;

            $handle = fopen($temp, "w");
            fwrite($handle, join("", soryArray($arr)));
            fclose($handle);
            
            $arr = array();
        }
    }
    fclose($file);

    return $listTemp;
}


/**
 * make 1 million integer 
 * write to file
 */
function make1mil($dir)
{
    $file = fopen($dir, 'w');
    for ($i = 0; $i < 100; $i++) {
        $str = makeInteger();
        fwrite($file, $str . "\n");
    }

    fclose($file);
}

/**
 * write string to file
 * a+ append
 */
function write($str, $dir, $option)
{
    $file = fopen($dir, 'a+');
    fwrite($file, $str . $option);
    fclose($file);
}

/**
 * make 10,000 integer
 * join to array to string
 * return string
 */
function makeInteger()
{
    $array = array();
    for ($i = 0; $i < 10000; $i++) {
        $array[] = rand(100, 999);
    }

    return join("\n", $array);
}

/**
 * sort array
 * return array
 */
function soryArray($array)
{
    //Create a bucket of arrays
    $bucket = array_fill(0, 9, array());
    $maxDigits = 0;
    //Determine the maximum number of digits in the given array.
    foreach ($array as $value) {
        $numDigits = strlen((string )$value);
        if ($numDigits > $maxDigits)
            $maxDigits = $numDigits;
    }
    $nextSigFig = false;
    for ($k = 0; $k < $maxDigits; $k++) {
        for ($i = 0; $i < count($array); $i++) {
            if (!$nextSigFig)
                $bucket[$array[$i] % 10][] = $array[$i];
            else
                $bucket[floor(($array[$i] / pow(10, $k))) % 10][] = $array[$i];
        }
        //Reset array and load back values from bucket.
        $array = array();
        for ($j = 0; $j < count($bucket); $j++) {
            foreach ($bucket[$j] as $value) {
                $array[] = $value;
            }
        }
        //Reset bucket
        $bucket = array_fill(0, 9, array());
        $nextSigFig = true;
    }
    return $array;
}

?>