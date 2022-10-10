<?php

namespace App\Helpers;

use App\Models\Task;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskHelper
{
    public static function todayTask()
    {
        $tasks =Task::where('user_id', Auth::user()->id)
        ->where(function ($query) {
                    $query->where('type', 'daily')->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('interval_type', 'date');
                            $query->where('end_date', '>=', today());
                        })
                        ->orWhere(function ($query) {
                            $query->where('interval_type', 'repetetion');
                            $query->whereRaw('repeat_count >= timestampdiff(DAY,created_at,now())');
                        });
                    });
                })
                ->orWhere(function ($query) {
                    $query->where('type', 'yearly');
                    $query->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->where('day', today()->day)->where('month', today()->month);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('end_date', '>=', today())->where('interval_type', 'date');
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(YEAR,created_at,now())')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })->orWhere(function ($query) {
                    $query->where('type', 'monthly');
                    $query->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->where('day', today()->day);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('end_date', '>=', today())->where('interval_type', 'date');
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(MONTH,created_at,now())')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })->orWhere(function ($query) {
                    $query->where('type', 'weekly')->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->where('day', today()->dayOfWeek);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('end_date', '>=', today())->where('interval_type', 'date');
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(WEEK,created_at,now())')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })
            ->get();
            return $tasks;
    }
    public static function nextDayTask()
    {
        $tasks = Task::where('user_id', Auth::user()->id)
        ->where(function ($query) {
                    $query->where('type', 'daily');
                    $query->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('end_date', '>=', today()->addDay(1))->where('interval_type', 'date');
                        })
                        ->orWhere(function ($query) {
                            $query->whereRaw('repeat_count >= timestampdiff(DAY,created_at,now()+INTERVAL 1 DAY)')->where('interval_type', 'repetetion');
                        });
                    });
                })   ->orWhere(function ($query) {
                    $query->where('type', 'yearly');
                    $query->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->where('day', today()->addDay(1)->day)->where('month', today()->addDay(1)->month);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('end_date', '>=', today()->addDay(1))->where('interval_type', 'date');
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(YEAR,created_at,now()+INTERVAL 1 DAY)')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })
                ->orWhere(function ($query)  {
                    $query->where('type', 'weekly')->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->where('day', today()->addDay(1)->dayOfWeek);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('interval_type', 'date')->where('end_date', '>=', today()->addDay(1));
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(WEEK,created_at,now()+INTERVAL 1 DAY)')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })
                ->orWhere(function ($query) {
                    $query->where('type', 'monthly');
                    $query->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->where('day', today()->addDay(1)->day);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('interval_type', 'date')->where('end_date', '>=', today()->addDay(1));
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(MONTH,created_at,now()+INTERVAL 1 DAY)')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })
             ->get();

        return $tasks;
    }
    public static function nextWeekTask()
    {
        $days = [];
        $months = [];
        $dates =  CarbonPeriod::create(today()->nextWeekday(), today()->nextWeekendDay())->toArray();

        foreach($dates as $date){
            $days[] = $date->day;
            if(!in_array($date->month, $months, true)){
                array_push($months, $date->month);
            }
        }

        $tasks = Task::where('user_id', Auth::user()->id)
        ->where(function ($query) use ($days, $months) {
                $query->where(function ($query) {
                    $query->where('type', 'daily')->orWhere('type','weekly')->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('interval_type', 'date')->whereBetween('end_date', [today()->addWeek()->startOfWeek(), today()->addWeek()->endOfWeek()]);
                        })
                        ->orWhere(function ($query) {
                            $query->whereRaw('repeat_count >= timestampdiff(DAY,created_at, now()+INTERVAL 7 - weekday(now())DAY)')->where('interval_type', 'repetetion');
                        });
                    });
                })
                ->orWhere(function ($query) {
                    $query->where('type', 'monthly')->where(function ($query) {
                        $query->whereHas('cycles', function ($query) {
                            $query->whereBetween('day', [today()->addWeek()->startOfWeek()->day, today()->addWeek()->endOfWeek()->day]);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('interval_type','date')->whereBetween('end_date', [today()->addWeek()->startOfWeek(),today()->addWeek()->endOfWeek()]);
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(MONTH,created_at, now()+INTERVAL 7 - weekday(now())DAY)')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                })
                ->orWhere(function ($query) use ($days, $months) {
                    $query->where('type', 'yearly')->where(function ($query) use ($days, $months) {
                        $query->whereHas('cycles', function ($query) use ($days, $months) {
                            $query->whereIn('day', $days)->whereIn('month', $months);
                        });
                        $query->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where('interval_type', 'date')->whereBetween('end_date', [today()->addWeek()->startOfWeek(),today()->addWeek()->endOfWeek()]);
                            })
                            ->orWhere(function ($query) {
                                $query->whereRaw('repeat_count >= timestampdiff(YEAR,created_at, now()+INTERVAL 7 - weekday(now())DAY)')->where('interval_type', 'repetetion');
                            });
                        });
                    });
                });
            })
            ->get();

        return $tasks;
    }



    public static function futureTask()
    {
        $secondWeekDay = today()->addWeeks(2)->startOfWeek();

        $dates =  CarbonPeriod::create(clone $secondWeekDay, clone $secondWeekDay->endOfWeek())->toArray();
        $days = [];
        $months = [];
        foreach($dates as $date){
            $days[] = $date->day;
            if(!in_array($date->month, $months, true)){
                array_push($months, $date->month);
            }
        }

        $tasks = Task::where('user_id', Auth::user()->id)
        ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('type', 'daily')->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('interval_type', 'date')->whereDate('end_date', '>', today()->addWeeks(2)->endOfWeek());
                        })
                        ->orWhere(function ($query) {
                            $query->whereRaw('repeat_count > timestampdiff(DAY,created_at, now()+INTERVAL 14 - weekday(now())DAY)')->where('interval_type', 'repetetion');
                        });
                    });
                })
                ->orWhere(function ($query) {
                    $query->orWhere('type', 'weekly')->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('interval_type', 'date')->whereDate('end_date', '>', today()->addWeeks(2)->endOfWeek());
                        })
                        ->orWhere(function ($query) {
                            $query->whereRaw('repeat_count > timestampdiff(WEEK,created_at, now()+INTERVAL 14 - weekday(now())DAY)')->where('interval_type', 'repetetion');
                        });
                    });
                })
                ->orWhere(function ($query) {
                    $query->where('type', 'yearly')->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('interval_type', 'date')->whereDate('end_date', today()->addWeeks(2)->endOfWeek());
                        })
                        ->orWhere(function ($query) {
                            $query->where('interval_type', 'repetetion')->whereRaw('repeat_count > timestampdiff(YEAR,created_at, now()+INTERVAL 14 - weekday(now())DAY)');
                        });
                    });
                });
            })
                ->orWhere(function ($query) {
                    $query->where('type', 'monthly')->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('interval_type', 'date')->whereDate('end_date', '>',today()->addWeeks(2)->endOfWeek());
                        })
                        ->orWhere(function ($query) {
                            $query->whereRaw('repeat_count > timestampdiff(MONTH,created_at, now()+INTERVAL 14 - weekday(now())DAY)')->where('interval_type', 'repetetion');
                        });
                    });
                })

            ->get();

            return $tasks;
    }
}
