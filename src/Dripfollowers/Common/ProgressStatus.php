<?php

namespace DripFollowers\Common;

interface ProgressStatus {
    const DONE = "DONE";
    const IN_PROGRESS = "IN PROGRESS";
    const ERROR = "ERROR";
    const RE_SCHEDULED = "RE SCHEDULED";
    const CANCELED = "CANCELED";
}