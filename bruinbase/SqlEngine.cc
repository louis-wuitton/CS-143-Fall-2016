/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <cstring>
#include <cstdlib>
#include <iostream>
#include <fstream>
#include <vector>
#include <limits.h>
#include "Bruinbase.h"
#include "SqlEngine.h"
#include "BTreeIndex.h"

using namespace std;

// external functions and variables for load file and sql command parsing
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}


//Be VERY CAREFUL: all conditions are ended together. Thus we need to use a min and max variable to get a range
RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning

  RC     rc;
  int    key;
  string value;
  int    count;
  int    diff;

  //We need non-equal values for some tricky cases such as key = '12' AND key <> '23'.
  //Since we don't want to read any page in this case
  vector<int> non_equals;
  vector<string> non_equal_vals;

  BTreeIndex newTree;
  SelCond temp;

  //Determine whether we should use B+ Tree
  bool useBPTree = false;
  bool isOpen = false;
  bool validQuery = false;


  bool keyEqual = false;
  bool hasMin = false;
  bool hasMax = false;


  bool valueEqual = false;
  bool vhasMin = false;
  bool vhasMax = false;

  int minKey = -1;
  int maxKey = -1;
  int equalKey = -1;


  bool valueCondition = false;
  string minString = "";
  string maxString = "";
  string equString = "";
  bool greaterE = false;
  bool lessE = false;



  IndexCursor m_cursor;

  //There is no point for using a B+ Tree if the key says not equal
  for (int i = 0; i < cond.size(); i++){
      //current condition
      temp = cond[i];
      if (temp.attr == 1){
        int currentVal = atoi(temp.value);
        if(currentVal == 2147483647){
          if(strcmp(temp.value, "2147483647") != 0){
            if(temp.comp == SelCond::GT || temp.comp == SelCond::GE || temp.comp == SelCond::EQ)
              goto found_all_we_need;
          }else{
              if(temp.comp == SelCond::GT)
                  goto found_all_we_need;
          }
        }
        else if(currentVal == INT_MIN){
          if(strcmp(temp.value, "-2147483648") != 0){
            if(temp.comp == SelCond::LT || temp.comp == SelCond::LE || temp.comp == SelCond::EQ)
              goto found_all_we_need;
          }else{
              if(temp.comp == SelCond::LT)
                  goto found_all_we_need;
          }
        }

        switch (temp.comp) {
          case SelCond::EQ:{
                if(!keyEqual){
                  keyEqual = true;
                  equalKey = currentVal;
                }else{
                  if (currentVal != equalKey)
                      goto found_all_we_need;
                }
                break;
              }
          case SelCond::NE:{
                if(keyEqual){
                  if (currentVal == equalKey)
                      goto found_all_we_need;
                }
                non_equals.push_back(currentVal);
                break;
              }
          case SelCond::GT:{
                //Specified key equal sth already
                if(keyEqual){
                  if (currentVal >= equalKey){
                     goto found_all_we_need;
                  }
                }else{
                  if(!hasMin){
                    hasMin = true;
                    minKey = currentVal + 1;
                  }else{
                      if(currentVal+1 > minKey){
                          minKey = currentVal + 1;
                      }
                  }
                }
                break;
              }
          case SelCond::LT:{
                if(keyEqual){
                  if(currentVal <= equalKey){
                    goto found_all_we_need;
                  }
                }
                else{
                    if(!hasMax){
                      hasMax = true;
                      maxKey = currentVal - 1;
                    }else{
                        if (currentVal - 1 < maxKey){
                          maxKey = currentVal - 1;
                        }
                    }
                }
                break;
              }
          case SelCond::GE:{
                  if(keyEqual){
                      if (currentVal > equalKey){
                          goto found_all_we_need;
                      }
                  }else{
                      if(!hasMin){
                          hasMin = true;
                          minKey = currentVal;
                      }else{
                          if(currentVal > minKey){
                            minKey = currentVal;
                          }
                      }
                  }
                  break;
              }
          case SelCond::LE:{
                if(keyEqual){
                  if(currentVal < equalKey){
                    goto found_all_we_need;
                  }
                }else{
                    if(!hasMax){
                      hasMax = true;
                      maxKey = currentVal;
                    }else{
                      if(currentVal < maxKey){
                        maxKey = currentVal;
                      }
                    }
                }
                break;
              }
        }
      }else{
          string currentVal = temp.value;
          valueCondition = true;
          switch (temp.comp) {
            case SelCond::EQ:{
                if(!valueEqual){
                    valueEqual = true;
                    equString = currentVal;
                }else{
                    if (strcmp(equString.c_str(), currentVal.c_str()) != 0)
                        goto found_all_we_need;
                }
                break;
            }
            case SelCond::NE:
                  break;
            case SelCond::GT:{
                if(!vhasMin){
                  vhasMin = true;
                  minString = currentVal;
                }else{
                  if(valueEqual){
                    if(strcmp(currentVal.c_str(), equString.c_str()) >= 0){
                      goto found_all_we_need;
                    }
                  }
                  if(strcmp(currentVal.c_str(), minString.c_str()) > 0){
                    greaterE = false;
                    minString = currentVal;
                  }
                }
                break;
            }
            case SelCond::LT:{
                if(!vhasMax){
                   vhasMax = true;
                   maxString = currentVal;
                }else{
                  if(valueEqual){
                    if(strcmp(currentVal.c_str(), equString.c_str()) <= 0){
                      goto found_all_we_need;
                    }
                  }else{
                    if(strcmp(currentVal.c_str(), maxString.c_str()) < 0){
                      lessE = false;
                      maxString = currentVal;
                    }
                  }
                }
                break;
            }
            case SelCond::GE:{
                if(!vhasMin){
                    vhasMin = true;
                    greaterE = true;
                    minString = currentVal;
                }else{
                    if(valueEqual){
                        if(strcmp(currentVal.c_str(), equString.c_str()) > 0){
                          goto found_all_we_need;
                        }
                    }else{
                        if(strcmp(currentVal.c_str(), minString.c_str()) > 0){
                          greaterE = true;
                          minString = currentVal;
                        }
                    }
                }
                break;
            }
            case SelCond::LE:{
              if(!vhasMax){
                 vhasMax = true;
                 lessE = true;
                 maxString = currentVal;
              }else{
                if(valueEqual){
                  if(strcmp(currentVal.c_str(), equString.c_str()) < 0){
                    goto found_all_we_need;
                  }
                }else{
                  if(strcmp(currentVal.c_str(), maxString.c_str()) < 0){
                    lessE = true;
                    maxString = currentVal;
                  }
                }
              }
              break;
            }
          }
      }
    }

  if (keyEqual){
    if(non_equals.size() > 0){
        for(int i = 0; i < non_equals.size(); i++){
          if(non_equals[i] == equalKey){
            goto found_all_we_need;
          }
        }
    }
    if((hasMin && equalKey < minKey)|| (hasMax && equalKey > maxKey)){
      goto found_all_we_need;
    }
  }
  if(hasMin && hasMax ){
    if (minKey > maxKey)
        goto found_all_we_need;
    if (minKey == maxKey){
      if(keyEqual){
        if(minKey != keyEqual){
          goto found_all_we_need;
        }
      }
    }
  }

  if(valueEqual){
      if(vhasMin){
        if(greaterE && strcmp(equString.c_str(), minString.c_str()) < 0)
          goto found_all_we_need;

        if(!greaterE && strcmp(equString.c_str(), minString.c_str()) <= 0)
          goto found_all_we_need;
      }
      if(vhasMax){
        if(lessE && strcmp(equString.c_str(), maxString.c_str()) > 0)
          goto found_all_we_need;

        if(!lessE && strcmp(equString.c_str(), maxString.c_str()) >= 0)
          goto found_all_we_need;
      }
  }

  if(vhasMin && vhasMax){
        if(greaterE && lessE){
          if(strcmp(minString.c_str(), maxString.c_str()) > 0){
              goto found_all_we_need;
          }
        }else{
          if(strcmp(minString.c_str(), maxString.c_str()) >= 0){
            goto found_all_we_need;
          }
        }
  }


  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }


  //If there is no index file, or for performance reason, we decided not to use B+ Tree

  if(attr == 1 || attr == 3){
    useBPTree = true;
    // You need to load the things in sorted order based on keys
  }
  else{
      if(keyEqual || hasMin || hasMax){
          useBPTree = true;
      }else{
          if(!valueCondition && attr == 4)
            useBPTree = true;
          else
            useBPTree = false;
      }
  }

  //You might not have index file
  if(!useBPTree || (rc = newTree.open(table+".idx", 'r')) != 0){
        rid.pid = rid.sid = 0;
        count = 0;
        bool finish_query = false;
        while (rid < rf.endRid()) {
            // read the tuple
            if ((rc = rf.read(rid, key, value)) < 0) {
                fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
                goto exit_select;
            }
            // check the conditions on the tuple
            for (unsigned i = 0; i < cond.size(); i++) {
              // compute the difference between the tuple value and the condition value
                switch (cond[i].attr) {
                    case 1:
                      diff = key - atoi(cond[i].value);
                      break;
                    case 2:
                      diff = strcmp(value.c_str(), cond[i].value);
                      break;
                }
                // skip the tuple if any condition is not met
                switch (cond[i].comp) {
                    case SelCond::EQ:
                      if (diff != 0) goto next_tuple;
                      break;
                    case SelCond::NE:
                      if (diff == 0) goto next_tuple;
                      break;
                    case SelCond::GT:
                      if (diff <= 0) goto next_tuple;
                      break;
                    case SelCond::LT:
                      if (diff >= 0) goto next_tuple;
                      break;
                    case SelCond::GE:
                      if (diff < 0) goto next_tuple;
                      break;
                    case SelCond::LE:
                      if (diff > 0) goto next_tuple;
                      break;
                }
            }
            // the condition is met for the tuple.
            // increase matching tuple counter
            count++;
            switch (attr) {
                case 1:  // SELECT key
                  fprintf(stdout, "%d\n", key);
                  break;
                case 2:  // SELECT value
                  fprintf(stdout, "%s\n", value.c_str());
                  break;
                case 3:  // SELECT *
                  fprintf(stdout, "%d '%s'\n", key, value.c_str());
                  break;
          }
          // move to the next tuple
          next_tuple:
          ++rid;
      }
  }else{
        isOpen = true;
        rid.pid = rid.sid = 0;
        count = 0;
        bool finish_query = false;

        if (keyEqual){
            newTree.locate(equalKey, m_cursor);
        }else if (hasMin) {
            newTree.locate(minKey, m_cursor);
        }else{
            newTree.locate(INT_MIN, m_cursor);
        }

        while(newTree.readForward(m_cursor, key, rid) == 0){
            if(attr == 4 and !valueCondition){
              if(keyEqual){
                if (key == equalKey){
                    count++;
                    goto found_all_we_need;
                }
                if(equalKey < key){
                  goto found_all_we_need;
                }
              }
              if(hasMin){
                  if(key < minKey){
                    goto found_all_we_need;
                  }
              }
              if(hasMax){
                if(key > maxKey){
                  goto found_all_we_need;
                }
              }

              bool skip_count = false;
              for(int i = 0; i < non_equals.size(); i++){
                if(key == non_equals[i]){
                  skip_count = true;
                }
              }
              if(!skip_count)
                  count++;
              continue;
            }

            if ((rc = rf.read(rid, key, value)) < 0) {
                fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
                goto exit_select;
            }
            // check the conditions on the tuple
            for (unsigned i = 0; i < cond.size(); i++) {
            // compute the difference between the tuple value and the condition value
                switch (cond[i].attr) {
                    case 1:
  	                   diff = key - atoi(cond[i].value);
  	                   break;
                   case 2:
  	                   diff = strcmp(value.c_str(), cond[i].value);
  	                    break;
                }
                // skip the tuple if any condition is not met
                switch (cond[i].comp) {
                    case SelCond::EQ:{
      	               if (diff != 0){
                         if(cond[i].attr == 1 && !valueCondition){
                           goto found_all_we_need;
                         }
                         if(keyEqual && equalKey == key){
                           goto found_all_we_need;
                         }
                          goto next_tuple_1;
                       }

                       if(!valueCondition){
                          finish_query = true;
                       }else{
                         if(cond[i].attr == 2 && equalKey == key){
                           finish_query = true;
                         }
                       }
      	               break;
                     }
                    case SelCond::NE:
      	               if (diff == 0){
                          if(keyEqual && equalKey == key){
                            goto found_all_we_need;
                          }
                          goto next_tuple_1;
                        }
                       if(keyEqual && equalKey == key){
                         finish_query = true;
                       }
      	               break;
                    case SelCond::GT:
      	               if (diff <= 0){
                          if(keyEqual && equalKey == key){
                            goto found_all_we_need;
                          }
                          goto next_tuple_1;
                       }
                       if(keyEqual && equalKey == key){
                         finish_query = true;
                       }
      	               break;
                    case SelCond::LT:{
      	               if (diff >= 0){
                          if(cond[i].attr == 1){
                              goto found_all_we_need;
                          }else{
                            if(keyEqual && equalKey == key){
                              goto found_all_we_need;
                            }
                          }
                          goto next_tuple_1;
                       }
                       if(keyEqual && equalKey == key){
                         finish_query = true;
                       }
      	               break;
                     }
                    case SelCond::GE:
      	               if (diff < 0) goto next_tuple_1;
      	               break;
                    case SelCond::LE:{
      	               if (diff > 0){
                          if(cond[i].attr == 1){
                            goto found_all_we_need;
                          }
                          goto next_tuple_1;
                       }
      	               break;
                     }
                }
              }
              count++;
              // print the tuple
              switch (attr) {
                case 1:  // SELECT key
                    fprintf(stdout, "%d\n", key);
                    if(finish_query){
                      goto found_all_we_need;
                    }
                    break;
                case 2:  // SELECT value
                    fprintf(stdout, "%s\n", value.c_str());
                    if(finish_query){
                      goto found_all_we_need;
                    }
                    break;
                case 3:  // SELECT *
                    fprintf(stdout, "%d '%s'\n", key, value.c_str());
                    if(finish_query){
                      goto found_all_we_need;
                    }
                    break;
              }
              // move to the next tuple
              next_tuple_1:
              //++rid;
              cout <<"";
        }
  }

  found_all_we_need:
  // print matching tuple count if "select count(*)"
  if (attr == 4) {
      fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  exit_select:

  if (isOpen){
    newTree.close();
  }

  rf.close();
  return rc;
}


RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */
  //For this project we modify the load function
  //if there is error while processing the file then we need to return the error code
  //Accodfing the spec we should use record file
  //Assume that index parameter is false at this point

  RecordFile rf;
  int key;
  string value;
  RecordId rid;
  RC rc = 0;
  string line;
  BTreeIndex newTree;

  ifstream fileData (loadfile.c_str());
  if(!fileData.is_open()){
      return -1;
  }

  if ((rc = rf.open(table+ ".tbl", 'w') != 0))
    return RC_FILE_OPEN_FAILED;

  //If index file exists
  if(index){
    newTree.open(table + ".idx", 'w');
    while(getline(fileData, line)){
      //append  the line into the record file
      if ((rc = parseLoadLine(line, key, value)) != 0){
        break;
      }
      if((rc = rf.append(key, value, rid)) != 0){
          return RC_FILE_WRITE_FAILED;
      }
      if(newTree.insert(key, rid) != 0)
           return RC_FILE_WRITE_FAILED;
    }
    newTree.close();
  }else{
    while(getline(fileData, line)){
      //append  the line into the record file
      if ((rc = parseLoadLine(line, key, value)) != 0){
        break;
      }
      if((rc = rf.append(key, value, rid)) != 0){
          return RC_FILE_WRITE_FAILED;
      }
    }
  }
  //close all the I/Os
  if (rf.close() != 0)
    return RC_FILE_CLOSE_FAILED;
  fileData.close();
  return rc;
}







RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
    const char *s;
    char        c;
    string::size_type loc;

    // ignore beginning white spaces
    c = *(s = line.c_str());
    while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
    key = atoi(s);

    // look for comma
    s = strchr(s, ',');
    if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
    do { c = *++s; } while (c == ' ' || c == '\t');

    // if there is nothing left, set the value to empty string
    if (c == 0) {
        value.erase();
        return 0;
    }

    // is the value field delimited by ' or "?
    if (c == '\'' || c == '"') {
        s++;
    } else {
        c = '\n';
    }

    // get the value string
    value.assign(s);
    loc = value.find(c, 0);
    if (loc != string::npos) { value.erase(loc); }

    return 0;
}
