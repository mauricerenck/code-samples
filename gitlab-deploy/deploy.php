<?php
/*
* @see http://www.kernelops.com/gitlab-post-receive-webhook/ for originial script
* @see http://maurice-renck.de/en/blog/gitlab-webhooks for details on config
*
*/

//you should make one of these scripts for every different project webhook you want - changing only the $wd variable
ini_set("log_errors", 1);
ini_set("error_log", "logs/hook.log");   //use this to log errors that are found in the script (change the filename and path to a log file of your choosing), the command will make the file automatically
error_reporting(E_ALL);
ignore_user_abort(true);            //don't want users screwing up the processing of the script by stopping it mid-process

// load config-file
$file = file_get_contents('deploy.json');
$data = json_decode($file);

//I'm sure there are other ways of handling the system call, this is just the method I've chosen
//setup the function to make the system call where $cwd is the "Current Working Directory"
function syscall ($cmd, $cwd) {
	$descriptorspec = array( 1 => array('pipe', 'w') ); // stdout is a pipe that the child will write to
	$resource = proc_open($cmd, $descriptorspec, $pipes, $cwd);
	if (is_resource($resource)) {
		$output = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		proc_close($resource);
		return $output;
	}
}

try {
	//I fumbled around for awhile to finally find this part: GitLab uses the $HTPP_RAW_POST_DATA server variable to house its JSON data meaning your typical $_REQUEST, $_POST, and $_GET will be EMPTY!
	if( $HTTP_RAW_POST_DATA ) {
		if( $oData = json_decode( $HTTP_RAW_POST_DATA ) ){ //transform the string into an object so we can get to the 'ref' element
			//now we want to split the ref string on "/", pop off the last element of the array (which will be the branch name), and do one quick validation to make sure a branch name actually exists
			//the actual 'ref' string will look like this: 'refs/heads/master' (or whatever branch was just pushed to instead of master)

			$project = (string)($oData->repository->name);
			$repoUrl = (string)($oData->repository->url);
			if(!isset($data->$project)) {
				throw new Exception("Project was not found in config-file");
			} else {

				$wd 	= $data->$project->path;
				$limits	= ($data->$project->limit != null) ? explode(',', $data->$project->limit) : null;

				if( ( $branch = array_pop( preg_split("/[\/]+/", $oData->ref) ) ) != "heads" ){
					if($limits == null || in_array($branch, $limits)) {

						if( is_dir( "$wd/$branch" ) ){   //lets check if branch dir exists
							//hey look, the branch directory already exists, so lets use it as our working directory and just run the pull command -- obviously we want to pull from the remote origin &amp; branch name
							$result = syscall("git pull origin $branch", "$wd/$branch");
						} else {
							//if branch dir doesn't exist, create it with a clone
							$result = syscall("git clone ".$repoUrl." $branch", $wd);
							//change dir to the clone directory, and checkout the branch
							$result = syscall("git checkout $branch", "$wd/$branch");
						}
					}
					return 1; //this isn't necessary but I put it here for good measure, just to say we are done and everything got executed properly
				} else {
					throw new Exception("branch variable is not set or == to 'heads'");
				}
			}
		} else {
			throw new Exception("An error was encountered while attempting to json_decode the HTTP_RAW_POST_DATA str");
		}
	} else {
		throw new Exception("HTTP_RAW_POST_DATA is not set or 'ref' is not a valid array element");
	}
} catch (Exception $e) {
	error_log( sprintf( "%s >> %s", date('Y-m-d H:i:s'), $e ) );
}