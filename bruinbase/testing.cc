#include <iostream>
#include <fstream>
#include "BTreeIndex.h"
#include "BTreeNode.h"
#include "PageFile.h"
#include "RecordFile.h"
#include <cstring>

using namespace std;

int parseLoadLine(const string& line, int& key, string& value)
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

int main(int argc, char* argv[]){
  BTreeIndex newTree;
  RecordFile rf;
  RecordId rid;
  string value;
  int key;

  cout << newTree.open("dummy.idx", 'w') << endl;
  //Open the file successfully

  //Now we need to insert into the page
  cout << newTree.getTreeHeight() << endl;
  string loadFile = "";
  ifstream tableData("xlarge.del");

  rf.open("movie1.tbl", 'w');

  if (!tableData.is_open())
    fprintf(stderr, "Error: loadfile %s cannot be opened\n", loadFile.c_str());

  string line;
  int count = 0;
  while (getline(tableData, line) && count <= 8000){
      parseLoadLine(line, key, value);
      if(rf.append(key, value, rid)!=0)
			     return -3;
      if(newTree.insert(key, rid) != 0)
           return -2;
      count ++;
  }

  cout <<"\nTotal number of lines are "<<count<< endl;

  cout << newTree.getTreeHeight()<<endl;
  cout << newTree.close() << endl;
  return 0;
}
