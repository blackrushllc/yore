#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define MAX_LINE_LEN 100
#define MAX_VARS 100

typedef struct {
    char name[20];
    int value;
} Variable;

Variable variables[MAX_VARS];
int var_count = 0;

// Function to get the value of a variable by name
int get_variable(char *name) {
    for (int i = 0; i < var_count; i++) {
        if (strcmp(variables[i].name, name) == 0) {
            return variables[i].value;
        }
    }
    return 0; // Default value for undefined variables
}

// Function to set or update the value of a variable
void set_variable(char *name, int value) {
    for (int i = 0; i < var_count; i++) {
        if (strcmp(variables[i].name, name) == 0) {
            variables[i].value = value;
            return;
        }
    }
    strcpy(variables[var_count].name, name);
    variables[var_count].value = value;
    var_count++;
}

// Function to interpret and execute a single line of BASIC code
void interpret_line(char *line) {
    char command[20], var_name[20];
    int value;

    if (sscanf(line, "PRINT %s", var_name) == 1) {
        printf("%d\n", get_variable(var_name));
    } else if (sscanf(line, "%s = %d", var_name, &value) == 2) {
        set_variable(var_name, value);
    } else if (sscanf(line, "%s = %s + %d", var_name, command, &value) == 3) {
        int var_value = get_variable(command);
        set_variable(var_name, var_value + value);
    } else {
        printf("Syntax error: %s\n", line);
    }
}

// Function to interpret code from a file
void interpret_file(FILE *file) {
    char line[MAX_LINE_LEN];
    while (fgets(line, sizeof(line), file)) {
        line[strcspn(line, "\n")] = 0; // Remove newline character
        interpret_line(line);
    }
}

// Function to enter interactive mode and process user input
void interactive_mode() {
    char line[MAX_LINE_LEN];

    printf("Entering BASIC interpreter interactive mode.\n");
    printf("Type your BASIC commands, or type 'EXIT' to quit.\n");

    while (1) {
        printf("> "); // Prompt symbol
        if (fgets(line, sizeof(line), stdin) == NULL) break;
        line[strcspn(line, "\n")] = 0; // Remove newline character

        // Exit the loop if the user types "EXIT"
        if (strcmp(line, "EXIT") == 0) {
            printf("Exiting interactive mode.\n");
            break;
        }

        interpret_line(line);
    }
}

int main(int argc, char *argv[]) {
    if (argc == 2) {
        // File input mode
        FILE *file = fopen(argv[1], "r");
        if (!file) {
            printf("Error opening file: %s\n", argv[1]);
            return 1;
        }
        interpret_file(file);
        fclose(file);
    } else {
        // Interactive mode
        interactive_mode();
    }

    return 0;
}
