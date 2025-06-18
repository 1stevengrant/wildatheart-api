<?php

namespace App\Enums;

enum ReportName: string
{
    case BAKR_HOSTS = 'getBAKRHosts';
    case WAHR_HOSTS = 'getWAHRHosts';
    case BAKR_EXPERIENCE_HOSTS = 'getBAKRExperienceHosts';
    case WAHR_EXPERIENCE_HOSTS = 'getWAHExperienceHosts';
    case FIRES_HOSTS = 'getFiresHosts';
    case CAPTIVATING_RETRAT_HOSTS = 'getCAPRHosts';
    case CAPTIVATING_EXPERIENCE_HOSTS = 'getCAPExperienceHosts';
    case SURVIVAL_SCHOOL_HOSTS = 'getSurvivalSchoolHosts';
}
